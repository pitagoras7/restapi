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
   
   
   
       public function file1($parametro = "")
       {
           if ($parametro == "") {
               $this->json = $this->json;
           } else {
               $this->json = $parametro;
           }
           $res.= "
   private $" . $this->table_ . "s = array();
   \n
   public function getAll" .ucfirst($this->table_) . "(){
   if(isset(\$_GET['". $this->json[1]['Field']."'])){
   \$". $this->json[1]['Field']." = \$_GET['". $this->json[1]['Field']."'];
   \$query = 'SELECT * FROM " . $this->table_ . " WHERE ". $this->json[1]['Field']." LIKE \"%' .\$". $this->json[1]['Field'].". '%\"';
   } else {
   \$query = 'SELECT * FROM " . $this->table_ . "';
   }
   \$dbcontroller = new DBController();
   \$this->" . $this->table_ . "s = \$dbcontroller->executeSelectQuery(\$query);
   return \$this->" . $this->table_ . "s;
   }
   ";
   
   
   $cadena1="";
   
   $res.="	public function add" .ucfirst($this->table_) . "(){\n";
   
   
   for ($i = 1; $i < count($this->json); $i++)
   {
   
     $res.="\n if(isset(\$_POST['". $this->json[$i]['Field']."'])){
     \$". $this->json[$i]['Field']." = \$_POST['". $this->json[$i]['Field']."'];
   }";
   
   $cadena1.=$this->json[$i]['Field'].",";
   $cadena2.= "'\".\$".$this->json[$i]['Field'].".\"'," ;
   }
   
   
   
     $res.="\n\n
   \$query = \"insert into tbl_mobile (".$cadena1.") values (".$cadena2.")\";
   \$dbcontroller = new DBController();
   \$result = \$dbcontroller->executeQuery(\$query);
   if(\$result != 0){
     \$result = array('success'=>1);
     return \$result;
   }
   
   \n }";
   
   
   
   
   
           return $res;
       }
   }
   
   $code = new codeModelo();
   
   
     $_POST['tabla']     = "tbl_mobile";
     $code->tabla_($_POST['tabla'],$_POST['prefijo']);
   
     echo $code->file1();
   
   
   ?>