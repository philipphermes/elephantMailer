<?php
declare(strict_types=1);

class Mailer
{
    private static string $mailTo;
    private static string $mailFrom;
    private static string $subject;
    private static array $data;

    private static array $attachments;
    private static string $style;

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

    /**
     * @throws \Exception
     */
    public static function addFile(string $filename, string $path): void
    {
        $filePath = $path . "/" . $filename;

        $attachment = file_get_contents($filePath);
        $attachment = chunk_split(base64_encode($attachment));

        if (array_key_exists($filename, self::$attachments)) {
            throw new Exception(sprintf("File: %s already added!", $filename));
        }

        self::$attachments[$filename] = $attachment;
    }

    /**
     * @param string $style
     * @return void
     * You can load the css contet from a file into this method
     */
    public static function addStyle(string $style): void
    {
        self::$style = sprintf("<style>%s</style>", $style);
    }

    public function sendMail(): bool
    {
        //TODO validate bevor sending

        $eol = "\r\n";

        // a random hash will be necessary to send mixed content
        $separator = md5((string)time());

        $headers = sprintf("From: %s%s", self::$mailFrom, $eol);
        $headers .= "MIME-Version: 1.0" . $eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
        $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
        $headers .= "This is a MIME encoded message." . $eol;

        $message = self::$style;

        $message .= "<body>";

        $message .= "--" . $separator . $eol;
        $message .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
        $message .= "Content-Transfer-Encoding: 8bit" . $eol;
        $message .= $message . $eol;

        foreach (self::$data as $element) {
            $message .= $element;
        }

        if (empty(self::$attachments)) {
            $message .= "--" . $separator . "--";
        } else {
            $message .= "--" . $separator . $eol;

            foreach (self::$attachments as $filename => $attachment) {
                $message .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
                $message .= "Content-Transfer-Encoding: base64" . $eol;
                $message .= "Content-Disposition: attachment" . $eol;
                $message .= $attachment . $eol;
            }

            $message .= "--" . $separator . "--";
        }

        $message .= "</body>";

        return mail(self::$mailTo, self::$subject, $message, $headers);
    }
}