<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email;

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Yiisoft\Log\Target;
use Yiisoft\Mailer\MailerInterface;

use function is_array;
use function is_string;
use function wordwrap;

/**
 * EmailTarget sends selected log messages to the specified email addresses.
 *
 * {@see EmailTarget::$mailer} is instance of {@see MailerInterface} that sends email and should be already configured.
 */
final class EmailTarget extends Target
{
    /**
     * @var MailerInterface The mailer instance.
     */
    private MailerInterface $mailer;

    /**
     * @var array|string The receiver email address.
     * @psalm-var array<array-key, string>|string
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format: `[email => name]`.
     */
    private $emailTo;

    /**
     * @var string The email message subject.
     */
    private string $subjectEmail;

    /**
     * @param MailerInterface $mailer The mailer instance.
     * @param array|string $emailTo The receiver email address.
     * @psalm-param array<array-key, string>|string $emailTo
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format: `[email => name]`.
     *
     * @param string $subjectEmail The email message subject.
     *
     * @throws InvalidArgumentException If the "to" email message argument is invalid.
     *
     * @psalm-suppress DocblockTypeContradiction
     */
    public function __construct(MailerInterface $mailer, $emailTo, string $subjectEmail = '')
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if (empty($emailTo) || (!is_string($emailTo) && !is_array($emailTo))) {
            throw new InvalidArgumentException('The "to" argument must be an array or string and must not be empty.');
        }

        $this->mailer = $mailer;
        $this->emailTo = $emailTo;
        $this->subjectEmail = $subjectEmail ?: 'Application Log';
        parent::__construct();
    }

    /**
     * Sends log messages to specified email addresses.
     *
     * @throws RuntimeException If the log cannot be exported.
     */
    protected function export(): void
    {
        $message = $this->mailer
            ->compose()
            ->withTo($this->emailTo)
            ->withSubject($this->subjectEmail)
            ->withTextBody(wordwrap($this->formatMessages("\n"), 70))
        ;

        try {
            $this->mailer->send($message);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to export log through email.', 0, $e);
        }
    }
}
