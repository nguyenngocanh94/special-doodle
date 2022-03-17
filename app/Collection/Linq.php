<?php
namespace App\Collection;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use ReflectionFunction;

/**
 * some funny function that inspire from LINQ C#
 * Class Collection
 */
final class Linq implements Countable, IteratorAggregate, ArrayAccess
{
    private $list;
    private $count = 0;
    private $skip = 0;
    private $take;
    // array hold intermediate ops
    /**
     * @var Op[]
     */
    private $intermediateFns;
    private $isTerminated = false;

    /**
     * Type of array 1 = index array, 2 = assoc array,
     * can't support both index and assoc
     * @var int
     */
    private $type;
    const INDEX = 1;
    const KEY_VALUE = 2;

    public function __construct()
    {
        $this->intermediateFns = [];
    }

    //region intermediate operations

    public function map($func): Linq
    {
        $info = new ReflectionFunction($func);
        $params = $info->getParameters();
        if (count($params)==1){
            $this->intermediateFns[] = new Op('map', $func);
        }else{
            $this->intermediateFns[] = new Op('mapKeyValue', $func);
        }

        return  $this;
    }
    //endregion

    private function execIntermediateOp($key,$item){
        $leng = count($this->intermediateFns);
        for ($i = 0; $i< $leng;++$i){
            $op = $this->intermediateFns[$i];
            if ($op->type=='map'){
                $item = ($op->function)($item);
            }else{
                $item = ($op->function)($key,$item);
            }

        }
        return $item;
    }


    private function execIntermediateOps(){
        if (!empty($this->intermediateFns)){
            $result = [];
            foreach ($this->list as $key => &$value){
                $value = $this->execIntermediateOp($key,$value);
                $result[] = $value;
            }
            //reset
            $this->intermediateFns = [];
            $this->setList($result);
            $this->isTerminated = true;
        }
    }

    //region terminal operations, because some technical problem then ...
    public function last(){
        $this->execIntermediateOps();
        return end($this->list);
    }

    public function forEach($travelFunc): Linq
    {
        foreach ($this->list as $key=>&$item){
            $item = $this->execIntermediateOp($key,$item);
            $travelFunc($item);
        }
        $this->isTerminated = true;
        $this->intermediateFns = [];
        return $this;
    }

    function sum($travelFunc) : float

    {
        $sum = 0;
        foreach ($this->list as $key=>&$item){
            $item = $this->execIntermediateOp($key,$item);
            $sum+=$travelFunc($item);
        }
        $this->isTerminated = true;
        $this->intermediateFns = [];
        return $sum;
    }
    /**

     * alias of ForEach
     * @param $travelFunc
     * @return $this
     */
    function each($travelFunc){
        return $this->forEach($travelFunc);
    }

    public function forEachKeyValue($travelFunc): Linq
    {
        foreach ($this->list as $key=>&$item){
            $item = $this->execIntermediateOp($key,$item);
            $travelFunc($key, $item);
        }
        $this->isTerminated = true;
        $this->intermediateFns = [];
        return $this;
    }

    public function first(){
        $this->execIntermediateOps();
        foreach ($this->list as $item) {
            return $item;
        }

        return null;
    }

    public function max(){
        $this->execIntermediateOps();
        return max($this->list);
    }

    public function maxByFunc($function){
        $this->execIntermediateOps();
        return Linq::from($this->list)->map($function)->max();
    }

    public function min(){
        $this->execIntermediateOps();
        return min($this->list);
    }

    public function toArray(): array
    {
        $this->execIntermediateOps();
        if (!isset($this->take) && $this->skip == 0){
            return $this->list;
        }
        $clone = $this->list;
        return array_splice($clone, $this->skip, $this->take);
    }

    public function filter($predicate): Linq
    {
        $result = [];
        foreach ($this->list as $key => &$value){
            $value = $this->execIntermediateOp($key,$value);
            if ($predicate($value)){
                $result[] = $value;
            }
        }
        $this->isTerminated = true;
        // reset
        $this->intermediateFns = [];
        $this->setList($result);
        return $this;
    }
    //endregion

    //region some utilities

    function contain($function): bool
    {
        $this->execIntermediateOps();
        foreach ($this->list as $key => $value){
            if ($function($value)){
                return true;
            }
        }
        return false;
    }

    function flatten() : self{
        $this->execIntermediateOps();
        $result = ArrayUtilities::flatten($this->list);
        return $this->setList($result);
    }

    public function append($element): Linq
    {
        if ($this->type == self::KEY_VALUE){
            throw new LinqException('append not work with key value linq');
        }
        $this->list[] = $element;
        $this->count++;
        return $this;
    }

    public function filterThenReturn($predicate): array
    {
        return array_filter($this->list, $predicate);
    }

    public function exclusiveOtherThenReturn(Linq $linq, $func) : array{
        return array_udiff($this->list, $linq->toArray(), $func);
    }

    private function setList($list): Linq
    {
        $this->list= $list;
        $this->count = count($this->list);
        if (is_assoc($list)){
            $this->type = self::KEY_VALUE ;
            return $this;
        }
        $this->type = 1;
        return $this;
    }

    public function sort($mode = 'asc'): Linq
    {
        if ($this->type==self::KEY_VALUE){
            if ($mode=='asc'){
                asort($this->list);
            }else{
                arsort($this->list);
            }
        }else{
            if ($mode=='asc'){
                sort($this->list);
            }else{
                rsort($this->list);
            }
        }

        return $this;
    }

    function sortByFunc(Closure $f): self
    {
        usort($this->list, $f);
        return  $this;
    }

    public function reverse() : self{
        if ($this->type == self::KEY_VALUE){
            throw new LinqException("cant not reverse key value");
        }
        $this->setList(array_reverse($this->list));
        return $this;
    }

    public function random(): Linq
    {
        shuffle($this->list);
        return $this;
    }

    public function groupByKey($key): Linq
    {
        if ($this->type==self::KEY_VALUE){
            throw new LinqException("groupByKey not support type assoc");
        }
        $newGroups = [];
        foreach ($this->list as $item){
            $newGroups[$item[$key]][] = $item;
        }

        $this->setList($newGroups);
        return $this;
    }

    public function distinct(){
        if ($this->type==self::KEY_VALUE){
            throw new LinqException("distinct ops not support type assoc");
        }
        $arr = array_unique($this->list);
        $this->setList($arr);
        return $this;
    }


    public function distinctBy($closure){
        if ($this->type==self::KEY_VALUE){
            throw new LinqException("distinct ops not support type assoc");
        }
        $res = [];
        foreach ($this->list as $item){
            $res[$closure($item)] = $item;
        }
        $this->setList(array_values($res));
        return $this;
    }

    public function groupBy(Closure $func){
        if ($this->type==self::KEY_VALUE){
            throw new LinqException("GroupBy ops not support type assoc");
        }
        $newGroups = [];
        foreach ($this->list as $item){
            $newGroups[$func($item)][] = $item;
        }
        $this->setList($newGroups);

        return $this;
    }


    // a quick funtion to paging
    public function setPage(Page $page) : self{
        $this->take($page->itemPerPages == -1 ? $this->count() : $page->itemPerPages);
        $this->skip($page->itemPerPages * ($page->page - 1));
        return $this;
    }
    #endregion

    //region factory method
    public static function from($arr): Linq
    {
        if ($arr instanceof Linq){
            $list = $arr->toArray();
            return Linq::from($list);
        }
        $a = new Linq();
        $a->setList($arr??[]);

        return $a;
    }

    public static function fromStr(string $arr, string $separated): Linq
    {
        $a = new Linq();
        $a->list = explode($separated, $arr);
        $a->type = self::INDEX;
        $a->count = count($a->list);
        return $a;
    }
    //endregion

    //region take - skip
    public function take(int $val = null): Linq
    {
        $this->take = $val;
        return $this;
    }

    public function skip(int $skip): Linq
    {
        $this->skip = $skip;
        return $this;
    }
    //endregion

    #region Countable, IteratorAggregate, ArrayAccess
    public function count(): int
    {
        return $this->count;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->list[$offset]) || array_key_exists($offset, $this->list);
    }

    public function offsetGet($offset)
    {
        return $this->list[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        if (! isset($offset)) {
            $this->list[] = $value;
            return;
        }

        $this->list[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (! isset($this->list[$offset]) && ! array_key_exists($offset, $this->list)) {
            return null;
        }

        $removed = $this->list[$offset];
        unset($this->list[$offset]);

        return $removed;
    }

    public function __clone()
    {
        $cloned = new self();
        $cloned->list = $this->list;
        $cloned->count = $this->count;
        $cloned->skip = $this->skip;
        $cloned->type = $this->type;
        try {
            $cloned->take = $this->take;
        }catch (\Throwable $exception){
            return $cloned;
        }

        return $cloned;
    }
    #endregion
}