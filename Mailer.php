<?php
declare(strict_types=1);

class Mailer
{
    private static string $mailTo;
    private static string $mailFrom;
    private static string $subject;
    private static array $data;

    public const H1 = 1;
    public const H2 = 2;
    public const H3 = 3;
    public const H4 = 4;

    public const UNORDERED_LIST = "ul";
    public const ORDERED_LIST = "ol";

    /**
     * @param string $mailTo
     * @return void
     * The email of the receiver
     */
    public static function setEmailTo(string $mailTo): void {
        self::$mailTo = $mailTo;
    }

    /**
     * @param string $mailFrom
     * @return void
     * The email of the sender
     */
    public static function setEmailFrom(string $mailFrom): void {
        self::$mailFrom = $mailFrom;
    }

    public static function setSubject(string $subject): void {
        self::$subject = $subject;
    }

    public static function addTitle(string $title, int $titleType = self::H1): void {
        self::$data[] = sprintf("<h%s>%s</h%s>", $titleType, $title, $titleType);
    }

    public static function addParagraph(string $paragraph): void {
        self::$data[] = sprintf("<p>%s</p>", $paragraph);
    }

    public static function addList(array $list, string $listType = self::UNORDERED_LIST): void {
        if (empty($list)) {
            throw new RuntimeException("List is empty!");
        }

        self::$data[] = sprintf("<%s>", $listType);

        foreach ($list as $element) {
            self::$data[] = sprintf("<li>%s</li>", $element);
        }

        self::$data[] = sprintf("</%s>", $listType);
    }

    public function sendMail(): bool
    {
        //TODO validate bevor sending

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= sprintf("From: %s\r\n". self::$mailFrom);

        $message = "";

        foreach (self::$data as $element) {
            $message .= $element;
        }

        return mail(self::$mailTo, self::$subject, $message, $headers);
    }
}