<?php

namespace App\Libraries;

class StrapiQueryBuilder
{
    protected array $query = [];

    public function sort(string $field, string $direction = 'asc')
    {
        $this->query['sort'][] = "{$field}:{$direction}";
        return $this;
    }

    public function filter(string $field, string $operator, $value)
    {
        $this->query['filters'][$field][$operator] = $value;
        return $this;
    }

    public function fields(array $fields)
    {
        $this->query['fields'] = $fields;
        return $this;
    }

    public function populate(string $relation, array|string $value='*')
    {
        $this->query['populate'][$relation] = $value;
        return $this;
    }

    public function populateFields(string $relation, array $fields)
    {
        $this->query['populate'][$relation]['fields'] = $fields;
        return $this;
    }

    public function pagination(int $page, int $pageSize)
    {
        $this->query['pagination'] = [
            'page'=>$page,
            'pageSize'=>$pageSize
        ];
        return $this;
    }

    public function status(string $status)
    {
        $this->query['status'] = $status;
        return $this;
    }

    public function locale(array $locales)
    {
        $this->query['locale'] = $locales;
        return $this;
    }

    public function raw(array $query)
    {
        $this->query = array_merge_recursive($this->query,$query);
        return $this;
    }

    public function build()
    {
        return http_build_query($this->query);
    }

    public function toArray()
    {
        return $this->query;
    }
}