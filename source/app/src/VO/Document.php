<?php

namespace App\VO;

use App\Exception\InvalidDocumentException;

class Document
{
    const MAX_FILE_SIZE = 10485760; // 10MB

    /**
     * @var string
     */
    private $id;

    /**
     * @var \SplFileInfo
     */
    private $file;

    /**
     * @var string
     */
    private $filepath;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \DateTimeImmutable
     */
    private $created;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $subject;

    /**
     * @param \SplFileInfo|null $file
     * @param SearchHit|null $hit
     */
    private function __construct(\SplFileInfo $file = null, SearchHit $hit = null)
    {
        if (!empty($file)) {
            $this->file = $file;
            $this->parse();
        }
        else {
            $this->id = $hit->getId();
            $this->filepath = $hit->getSourceField('filepath');
            $this->filename = $hit->getSourceField('filename');
            $this->from = $hit->getSourceField('from');
            $this->to = $hit->getSourceField('to');
            $this->subject = $hit->getSourceField('subject');
            $this->created = \DateTimeImmutable::createFromFormat('Y-m-d', $hit->getSourceField('created'));
        }
    }

    /**
     * @param \SplFileInfo $file
     * @return Document
     */
    public static function fromFile(\SplFileInfo $file): Document
    {
        return new self($file);
    }

    /**
     * @param SearchHit $hit
     * @return Document
     */
    public static function fromSearchHit(SearchHit $hit)
    {
        return new self(null, $hit);
    }

    /**
     *
     */
    private function parse()
    {
        $filename = pathinfo($this->getFileName(), PATHINFO_FILENAME);

        preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $filename, $matches);
        if (empty($matches)) {
            throw new InvalidDocumentException($this->file, ' does not contain a valid date');
        }
        $this->created = \DateTimeImmutable::createFromFormat('Y-m-d', $matches[0]);

        $data = array_slice(explode(' - ', $filename), 1);
        switch (count($data)) {
            case 2:
                $this->from = $data[0];
                $this->subject = $data[1];
                break;
            case 3:
                $this->from = $data[0];
                $this->to = $data[1];
                $this->subject = $data[2];
                break;
            default:
                throw new InvalidDocumentException($this->file, ' does not contain valid data '.count($data));
        }
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getFilepath(): string
    {
        if (!empty($this->file) && empty($this->filepath)) {
            $this->filepath = $this->file->getRelativePathname();
        }

        return $this->filepath;
    }

    /**
     * @return string
     */
    public function getFilepathDownload(): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->getFilepath());
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        if (!empty($this->file) && empty($this->filename)) {
            $this->filename = pathinfo($this->getFilepath(), PATHINFO_BASENAME);
        }

        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        if (isset($this->id)) {
            return $this->id;
        }

        return sha1($this->file->getRelativePathname());
    }

    /**
     * @param bool $useData
     * @return array
     */
    public function getBody($useData = true): array
    {
        $body = [
            'filepath' => $this->getFilePath(),
            'filename' => $this->getFileName(),
            'created' => $this->created->format('Y-m-d'),
            'from' => $this->from,
            'to' => $this->to,
            'subject' => $this->subject,
        ];

        if ($useData && $this->file->getSize() < self::MAX_FILE_SIZE) {
            $body['attachment_data'] = base64_encode($this->file->getContents());
        }

        return $body;
    }
}
