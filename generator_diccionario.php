<?php
require_once 'padreModelo.php';
class codeModelo extends padreModelo
{
    public $json = "";
    public $table_ = "";
    public $pre = "";
    public $name_ = "";

    public function tabla_($tabla, $pre = "")
    {
        $obj          = new padreModelo();
        $sql          = "SHOW COLUMNS FROM " . $tabla;
        $data         = $obj->ejecutar_query($sql);
        $this->json   = $data;
        $this->table_ = $tabla;
        if ($pre == "") {
            $this->pre = substr($tabla, 0, 3);
        } else {
            $this->pre = $pre;
        }

    }


    public function alltables($db)
    {
        $obj          = new padreModelo();
        $sql          = "SHOW FULL TABLES FROM " . $db;
        $data         = $obj->ejecutar_query($sql);

        $res.=" {   ";
        
        for($i=0;$i<count($data);$i++){
          
          $res.= " \" ".$data[$i]['Tables_in_ibuhotel']." \": {";
          $this->tabla_($data[$i]['Tables_in_ibuhotel']);
          $res.= $this->diccionario1();
          $res.="  }, ";
        }
  
        $res.=" } ";

        return $res;

    }




    public function diccionario($parametro = "")
    {
    $res=" {  \"plan\": { ";
    
    for ($i = 0; $i < count($this->json); $i++)
    {
    $res.=" \"". $this->json[$i]['Field']."\": \"\", ";
    }

 $res.=" } }";
        return $res;
    }


    public function diccionario1($parametro = "")
    {
   
    
    for ($i = 0; $i < count($this->json); $i++)
    {

        $coma  = ",";
        if($i == ( count($this->json) - 1 )  ){
        $coma  = "";
        }

        

    $res.=" \"". $this->json[$i]['Field']."\": \"\"   ".$coma;
    }

    return $res;
    
    }








}

$code = new codeModelo();

 $code->tabla_("ocupation");
 echo $code->diccionario();

//echo $code->alltables("ibuhotel");



?>
