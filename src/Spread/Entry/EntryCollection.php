<?php

namespace Spy\Timeline\Spread\Entry;

use Spy\Timeline\Driver\ActionManagerInterface;

class EntryCollection implements \IteratorAggregate
{
    /**
     * @var ActionManagerInterface
     */
    protected $actionManager;

    /**
     * @var \ArrayIterator
     */
    protected \Traversable $coll;

    protected int $batchSize;

    /**
     * @param boolean $duplicateOnGlobal Each timeline action are automatically pushed on Global context
     * @param integer $batchSize         batch size
     */
    public function __construct(protected bool $duplicateOnGlobal = true, $batchSize = 50)
    {
        $this->coll              = new \ArrayIterator();
        $this->batchSize         = (int) $batchSize;
    }

    /**
     * @param ActionManagerInterface $actionManager actionManager
     */
    public function setActionManager(ActionManagerInterface $actionManager): void
    {
        $this->actionManager = $actionManager;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return $this->coll;
    }

    /**
     * @param EntryInterface $entry   entry you want to push
     * @param string         $context context where you want to push
     */
    public function add(EntryInterface $entry, $context = 'GLOBAL'): void
    {
        if (!isset($this->coll[$context])) {
            $this->coll[$context] = [];
        }

        $this->coll[$context][$entry->getIdent()] = $entry;
        if (!$this->duplicateOnGlobal) {
            return;
        }

        if ($context === 'GLOBAL') {
            return;
        }

        $this->add($entry);
    }

    /**
     * Load unaware entries, instead of having 1 call by entry to fetch component
     * you can add unaware entries. Component will be created or exception
     * will be thrown if it does not exist
     *
     * @throws \Exception
     * @return void
     */
    public function loadUnawareEntries()
    {
        if (!$this->actionManager) {
            return;
        }

        $unawareEntries = [];

        foreach ($this->coll as $entries) {
            foreach ($entries as $entry) {
                if ($entry instanceof EntryUnaware) {
                    $unawareEntries[$entry->getIdent()] = $entry->getIdent();
                }
            }
        }

        if ($unawareEntries === []) {
            return;
        }

        $components = $this->actionManager->findComponents($unawareEntries);
        $componentsIndexedByIdent = [];
        foreach ($components as $component) {
            $componentsIndexedByIdent[$component->getHash()] = $component;
        }

        unset($components);

        $nbComponentCreated = 0;
        foreach ($this->coll as $entries) {
            foreach ($entries as $entry) {
                if ($entry instanceof EntryUnaware) {
                    $ident = $entry->getIdent();
                    // component fetched from database.
                    if (array_key_exists($ident, $componentsIndexedByIdent)) {
                        $entry->setSubject($componentsIndexedByIdent[$ident]);
                    } else {
                        if ($entry->isStrict()) {
                            throw new \Exception(sprintf('Component with ident "%s" is unknown', $entry->getIdent()));
                        }

                        // third argument ensures component is not flushed directly.
                        $component = $this->actionManager->createComponent($entry->getSubjectModel(), $entry->getSubjectId(), false);

                        ++$nbComponentCreated;

                        if (($nbComponentCreated % $this->batchSize) == 0) {
                            $this->actionManager->flushComponents();
                        }

                        if (null === $component) {
                            throw new \Exception(sprintf('Component with ident "%s" cannot be created', $entry->getIdent()));
                        }

                        $entry->setSubject($component);
                        $componentsIndexedByIdent[$component->getHash()] = $component;
                    }
                }
            }
        }

        if ($nbComponentCreated > 0) {
            $this->actionManager->flushComponents();
        }
    }

    /**
     * @param boolean $v v
     */
    public function setDuplicateOnGlobal(bool $v): void
    {
        $this->duplicateOnGlobal = $v;
    }

    /**
     * Clear entries
     */
    public function clear(): void
    {
        $this->coll = new \ArrayIterator();
    }
}
