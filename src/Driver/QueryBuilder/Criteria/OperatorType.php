<?php

namespace Spy\Timeline\Driver\QueryBuilder\Criteria;

enum OperatorType: string
{
    case AND = 'AND';
    case OR = 'OR';
}
