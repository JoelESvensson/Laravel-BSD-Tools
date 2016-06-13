<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Carbon\Carbon;

class ToSortedAssociative
{

    private $compFunc;
    public function __construct(callable $compFunc)
    {
        $this->compFunc = $compFunc;
    }

    public function __invoke(array $data): array
    {
        $result = [];
        foreach ($data['done'] as $key => $value) {
            $result[$key] = $value['data'];
        }

        uksort($result, $this->compFunc);
        return $result;
    }
}
