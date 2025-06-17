<?php
namespace App\Filters;
use Illuminate\Http\Request;


class ApiFilter{

    public function transform(Request $request){
        
      
        // $eloQuery = [];
        // foreach($this->safeParams as $param => $operators){
        //     $query = $request->query($param);
        //     if (!isset($query)){
        //         continue;
        //     }
        //     $column = $this->columnMap[$param] ?? $param;
        //     foreach($operators as  $operador){
        //         if (isset($query[$operador])){
        //             $eloQuery[] = [$column, $this->operatorMap[$operador], $query[$operador] ];
        //         }
        //     }
        // }
        // return $eloQuery;
    }
}