<?php

namespace Spy\Timeline\Driver\QueryBuilder;

use Spy\Timeline\Driver\ActionManagerInterface;
use Spy\Timeline\Driver\QueryBuilder\Criteria\CriteriaInterface;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Operator;
use Spy\Timeline\Model\Component;
use Spy\Timeline\Model\ComponentInterface;

class QueryBuilder
{
    protected array $subjects = [];
    protected int $page = 1;
    protected int $maxPerPage = 10;
    protected array $criterias = [];
    /**@var array(<string>$field, <string>$way) */
    protected array $sort = [];
    protected QueryBuilderFactory $factory;
    protected static array $fieldLocation = ['context' => 'timeline', 'createdAt' => 'action', 'verb' => 'action', 'type' => 'actionComponent', 'text' => 'actionComponent', 'model' => 'component', 'identifier' => 'component'];

    public function __construct(QueryBuilderFactory $factory = null)
    {
        if (null === $factory) {
            $factory = new QueryBuilderFactory();
        }

        $this->factory = $factory;
    }

    public function logicalAnd(): Operator
    {
        return $this->createNewOperator(Operator::TYPE_AND, func_get_args());
    }

    public function logicalOr(): Operator
    {
        return $this->createNewOperator(Operator::TYPE_OR, func_get_args());
    }

    public function field(string $field)
    {
        if (!in_array($field, $this->getAvailableFields(), true)) {
            throw new \InvalidArgumentException(sprintf('Field "%s" not supported, prefer: %s', $field, implode(', ', $this->getAvailableFields())));
        }

        return $this->factory
            ->createAsserter()
            ->field($field)
        ;
    }

    public function createNewOperator(string $type, array $args): Operator
    {
        if ($args === [] || count($args) < 2) {
            throw new \InvalidArgumentException(__METHOD__.' accept minimum 2 arguments');
        }

        return $this->factory
            ->createOperator()
            ->setType($type)
            ->setCriterias($args)
        ;
    }

    public function addSubject(ComponentInterface $component): static
    {
        $this->subjects[$component->getHash()] = $component;

        return $this;
    }

    public function getSubjects(): array
    {
        return $this->subjects;
    }

    /**
     * @param CriteriaInterface $criteria criteria
     *
     * @return QueryBuilder
     */
    public function setCriterias(CriteriaInterface $criteria): static
    {
        $this->criterias = $criteria;

        return $this;
    }

    /**
     * @return CriteriaInterface|null
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @param integer $page page
     *
     * @return QueryBuilder
     */
    public function setPage($page)
    {
        $this->page = (int) $page;

        return $this;
    }

    /**
     * @param string $maxPerPage maxPerPage
     *
     * @return QueryBuilder
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = (int) $maxPerPage;

        return $this;
    }

    /**
     * @param string $field field
     * @param string $order order
     *
     * @return QueryBuilder
     */
    public function orderBy($field, $order)
    {
        if (!in_array($field, $this->getAvailableFields())) {
            throw new \InvalidArgumentException(sprintf('Field "%s" not supported, prefer: %s', $field, implode(', ', $this->getAvailableFields())));
        }

        if (!in_array($order, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException(sprintf('Order "%s" not supported, prefer: ASC or DESC', $order));
        }

        $this->sort = [$field, $order];

        return $this;
    }

    /**
     * @param string $field    field
     * @param string $location location
     */
    public function addFieldLocation($field, $location): void
    {
        self::$fieldLocation[$field] = $location;
    }

    /**
     * @param string $field field
     *
     * @return string
     */
    public static function getFieldLocation($field)
    {
        return self::$fieldLocation[$field];
    }

    public function getAvailableFields(): array
    {
        return array_keys(self::$fieldLocation);
    }

    /**
     * @param  array                  $data          data
     * @param ActionManagerInterface|null $actionManager actionManager
     * @throws \Exception
     * @return $this
     */
    public function fromArray(array $data, ActionManagerInterface $actionManager = null)
    {
        if (isset($data['criterias']['type'])) {
            $criterias = $data['criterias'];
            $type      = $criterias['type'];

            if ('operator' === $type) {
                $method = 'createOperatorFromArray';
            } elseif ('expr' === $type) {
                $method = 'createAsserterFromArray';
            } else {
                throw new \Exception('Invalid array, cannot be unserialized');
            }

            $this->setCriterias($this->factory->{$method}($criterias));
        }

        if (isset($data['page'])) {
            $this->setPage($data['page']);
        }

        if (isset($data['max_per_page'])) {
            $this->setMaxPerPage($data['max_per_page']);
        }

        if (isset($data['sort'])) {
            [$field, $order] = $data['sort'];
            $this->orderBy($field, $order);
        }

        if (isset($data['subject']) && !empty($data['subject'])) {
            $subjects = $data['subject'];

            if ($actionManager === null) {
                throw new \Exception('Please provide the actionManager to retrieve components');
            }

            $components = $actionManager->findComponents($subjects);

            if ((is_countable($components) ? count($components) : 0) !== (is_countable($subjects) ? count($subjects) : 0)) {
                foreach ($components as $component) {
                    // remove existing components from subjects to keep only new one components
                    unset($subjects[array_search($component->getHash(), $subjects, true)]);
                }

                // create new components
                foreach ($subjects as $subject) {
                    [$model, $identifier] = explode('#', (string) $subject);
                    $components[] = $actionManager->createComponent($model, unserialize($identifier));
                }
            }

            foreach ($components as $component) {
                $this->addSubject($component);
            }
        }

        return $this;
    }

    /**
     * @return array{subject: mixed[], page: int, max_per_page: int, criterias: mixed, sort: mixed[]}
     */
    public function toArray(): array
    {
        return ['subject'         => array_values(
            array_map(static fn ($v) => $v->getHash(), $this->subjects)
        ), 'page'            => $this->page, 'max_per_page'    => $this->maxPerPage, 'criterias'       => $this->criterias ? $this->criterias->toArray() : null, 'sort'            => $this->sort];
    }
}
