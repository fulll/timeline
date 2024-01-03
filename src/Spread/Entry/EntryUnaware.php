<?php

namespace Spy\Timeline\Spread\Entry;

use Spy\Timeline\Model\ComponentInterface;

class EntryUnaware implements EntryInterface
{
    protected string $subjectId;

    /**
     * @var ComponentInterface
     */
    protected $subject;

    /**
     * @param string  $subjectModel subjectModel
     * @param string  $subjectId    subjectId
     * @param boolean $strict       If strict (component fetch is mandatory,
     *                              if nothing is returned, exception will be thrown)
     */
    public function __construct(protected string $subjectModel, $subjectId, protected bool $strict = false)
    {
        if (is_scalar($subjectId)) {
            $subjectId = (string) $subjectId;
        } elseif (!is_array($subjectId)) {
            throw new \InvalidArgumentException('subjectId has to be a scalar or an array');
        }

        $this->subjectId    = $subjectId;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdent()
    {
        return $this->subjectModel.'#'.serialize($this->subjectId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param ComponentInterface $subject subject
     */
    public function setSubject(ComponentInterface $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubjectModel(): string
    {
        return $this->subjectModel;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }
}
