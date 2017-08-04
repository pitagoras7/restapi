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



  public function inicial(){



    $res.="<?php\n\n
    require_once 'Conexion.php';\n\n

    class ".$this->table_."Model extends conexion\n
    {\n\n\n\n";

    $res.="public \$table         = \"".$this->table_."\"; \n";
    $res.="public \$request       = \"\";\n\n ";

    for ($i = 0; $i < count($this->json); $i++)
    {

        $res.="public \$". $this->json[$i]['Field']." = null;\n";
        $res.="public \$". $this->json[$i]['Field']."_field = \"". $this->json[$i]['Field']."\"; \n\n";

    }




    $res.="\n\n\npublic function esqueleto(){ \n\n\n";

    
    for ($i = 0; $i < count($this->json); $i++)
    {

        if($this->json[$i]['Field']!="id"){

            $res.="if( \$this->".$this->json[$i]['Field']." !== null ){ \n";
            $res.="\$this->where_value__[] = \$this->".$this->json[$i]['Field'].";\n";
            $res.="\$this->where_field__[] = \$this->".$this->json[$i]['Field']."_field;\n";
            $res.="  }\n\n";
            
        }

    }

    $res.=" \n\n}";



    $res.="\n\n\npublic function save(\$id=null)\n
    {\n
        \$this->id = \$id;\n
        \$this->esqueleto();\n
        return \$this->ejecucion();\n
    }\n\n\n\n";


    $res.="}";

    echo $res;


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



public function modal(){

    for ($i = 0; $i < count($this->json); $i++)
    {


        $res.="<div class=\"input-field col m6 s12\">
        <input  placeholder=\"\" name=\"".$this->json[$i]['Field']."\" id=\"".$this->json[$i]['Field']."\" type=\"text\" >
        <label>".$this->json[$i]['Field']."</label>
    </div>\n";

}

$res.="\n\n\n";

for ($i = 0; $i < count($this->json); $i++)
{


    $res.="\$m->".$this->json[$i]['Field']." = \$_PUT['".$this->json[$i]['Field']."'];\n";

}


$res.="\n\n\n";


for ($i = 0; $i < count($this->json); $i++)
{


    $res.=" \$(\"#formUpdate".ucfirst($this->table_)." #".$this->json[$i]['Field']." \").val(n.".$this->json[$i]['Field']."); \n";

}



$res.="\n\n\n";

$res.="<table id='datatableLocations__' class='display responsive-table datatable-example'><thead><tr>";
for ($i = 0; $i < count($this->json); $i++)
{
    $res.="<th class=' displayField_".$this->json[$i]['Field']."  displayField".$this->json[$i]['Field']."'><span class='DataTableHeader_".$this->json[$i]['Field']." ' >".$this->json[$i]['Field']."</span></th>";
}

$res.="<th></th></tr></thead><tbody class='tbodydata' ></tbody></table>";



$res.="\n\n\n";

for ($i = 0; $i < count($this->json); $i++)
{
    $res.="<td class='DataTableField_".$this->json[$i]['Field']." displayField_".$this->json[$i]['Field']."'> \"+ n.".$this->json[$i]['Field']." +\"</td>";
}



$res.="\n\n\n";

for ($i = 0; $i < count($this->json); $i++)
{
    $res.="\$(\".DataTableHeader_".$this->json[$i]['Field']." \").text('".$this->json[$i]['Field']."');
";
}

$res.="\n\n\n";

for ($i = 0; $i < count($this->json); $i++)
{

     $res.="\$(' #datatableLocations__ .displayField_".$this->json[$i]['Field']."').css('display','none');\n";

}


           


$res.="\n\n\n";





echo $res."\n\n\n";


}




}

$code = new codeModelo();

$code->tabla_("orderExtras");
$code->modal();
//echo $code->diccionario();
//$code->inicial();
//echo $code->alltables("ibuhotel");



?>
