<?php

require_once 'Conexion.php';

class padreModelo extends conexion {

    private $id;
    private $id_padre;
    private $tabla;
    private $nuevo;
    private $sql_campo;
    private $sql_valor;
    private $correlativo;
    private $numero_documento;
    private $sigla;
    public $estatu = "true";
    public $bd = "pg";
    public $motorbd = "mysql";
    public $ultimoId;

    public function __construct($bd = "pg")
    {
        $this->bd = $bd;
    }

    public function fecha_alreves($fecha)
    {
        $dd = substr($fecha, 0, 2);
        $mm = substr($fecha, 3, 2);
        $yy = substr($fecha, 6, 4);
        $fecha = $yy . "/" . $mm . "/" . $dd;
        return $fecha;
    }

    public function vacio($var, $msj = "")
    {

        if (strlen($var) > 0)
        {

            return false;
        }
        else
        {

            if (strlen($msj) > 0)
            {
                echo $msj;
            }

            return true;
        }
    }

    public function novacio($var)
    {

        if (strlen($var) > 0)
            return true;
        else
            return false;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTabla($tabla)
    {
        $this->tabla = $tabla;
    }

    public function setConfig($tabla, $id = null)
    {
        $this->tabla = $tabla;
        $this->id = $id;

        if (!$id)
        {
            $this->nuevo = true;
//            $operacion = 'nuevo';
        }
        else
        {
//            $operacion = 'editar';
        }
//        $this->historico($this->id, $operacion);
    }

    public function getId()
    {
        return $this->id;
    }

    public function ejecutar_query01($query)
    {

        if ($this->bd == 'pg')
        {
            $this->abrirConexionPg();
            $this->sql = $query;
            $data = $this->ejecutarSentenciaPg01(2);
            return $data;
        }

        if ($this->bd == 'mysql')
        {
            $this->abrirConexionMysql();
            $this->sql = $query;
            $data = $this->ejecutarSentenciaMysql(2);
            return $data;
        }
    }

    public function ejecutar_query02($query)
    {

        $this->abrirConexionMysql();
        $this->sql = $query;
        $data = $this->ejecutarSentenciaMysql(2);
        return $data;
    }

    public function ejecutar_query($sql)
    {

        $this->sql = $sql;

        if ($this->motorbd == "mysql")
            return $this->ejecutarSentenciaMysql();
        if ($this->motorbd == "pg")
            return $this->ejecutarSentenciaPg(2);
    }

    public function add_data($campo, $valor, $strtoupper = TRUE)
    {

        if ($strtoupper == 'false')
        {
            strlen($valor) <= 0 ? $valor = 'null , ' : $valor = "'" . $valor . "' , ";
        }
        else
        {
            strlen($valor) <= 0 ? $valor = 'null , ' : $valor = "'" . trim($strtoupper ? strtoupper(strtr($valor, "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ")) : $valor) . "', ";
        }


        if (isset($this->nuevo))
        {//Nuevo Registro
            $this->sql_campo.=$campo . ',';
            $this->sql_valor.=$valor;
        }
        else
        {//Actualización de Registro
            $this->sql_valor.= $campo . " = " . $valor;
        }
    }

    public function add_($campo, $valor, $strtoupper = TRUE)
    {

        strlen($valor) <= 0 ? $valor = false : $valor = "'" . trim(pg_escape_string($strtoupper ? strtoupper(strtr($valor, "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ")) : $valor)) . "', ";
        if ($valor != false)
        {
            if (isset($this->nuevo))
            {//Nuevo Registro
                $this->sql_campo.=$campo . ',';
                $this->sql_valor.=$valor;
            }
            else
            {//Actualización de Registro
                $this->sql_valor.= $campo . " = " . $valor;
            }
        }
    }

    public function ejecutar($tipo_id = 'id')
    {



        if ($this->motorbd == "pg")
        {

            $this->abrirConexionPg();
            if (isset($this->nuevo))
            {//Nuevo Registro
                $this->sql = " INSERT INTO " . $this->tabla . " (" . $this->sql_campo . "registro,estatu) VALUES (" . $this->sql_valor . "'now()','" . $this->estatu . "'); ";
            }
            else
            {//Actualización de Registro
                $this->sql = "UPDATE  " . $this->tabla . "  SET  " . substr($this->sql_valor, 0, -2);
                $this->sql.= " WHERE " . $tipo_id . " in (" . $this->id . ")";
            }
            $this->ejecutarSentenciaPg(2);
            if (isset($this->nuevo))//Nuevo Registro
                $this->id = $this->verId($this->tabla);
            unset($this->nuevo, $this->sql_campo, $this->sql_valor);

            $this->ultimoId = $this->verId($this->tabla);
        }


        if ($this->motorbd == "mysql")
        {


            $this->abrirConexionMysql();

            if (isset($this->nuevo))
            {
                $this->sql_valor = substr(trim($this->sql_valor), 0, -1);
                $this->sql_campo = substr(trim($this->sql_campo), 0, -1);
            }

            if (isset($this->nuevo))
            {//Nuevo Registro
                $this->sql = " INSERT INTO " . $this->tabla . " (" . $this->sql_campo . ") VALUES (" . $this->sql_valor . "); ";
            }
            else
            {//Actualización de Registro
                $this->sql = "UPDATE  " . $this->tabla . "  SET  " . substr($this->sql_valor, 0, -2);
                $this->sql.= " WHERE " . $tipo_id . " in (" . $this->id . ")";
            }
            $this->ejecutarSentenciaMysql();
            if (isset($this->nuevo))//Nuevo Registro
                $this->id = $this->verIdMysql();
            unset($this->nuevo, $this->sql_campo, $this->sql_valor);

            $this->ultimoId = $this->verIdMysql();
        }
    }

    public function _update_sql($sql)
    {


        if ($this->motorbd == "pg")
        {

            $this->abrirConexionPg();
            $this->sql = $sql;
            $this->ejecutarSentenciaPg(2);
            $this->id = $this->verId($this->tabla);
            $this->ultimoId = $this->verId($this->tabla);
        }


        if ($this->motorbd == "mysql")
        {

            $this->abrirConexionMysql();
            $this->sql = $sql;
            $this->ejecutarSentenciaMysql();
            $this->id = $this->verIdMysql();
            $this->ultimoId = $this->verIdMysql();
        }
    }

    public function get_ultimoId($tabla)
    {

        if ($this->motorbd == "mysql")
            return $this->verIdMysql();
        if ($this->motorbd == "pg")
            return $this->verIdPg($tabla);
    }

    public function verId($tabla)
    {

        if ($this->motorbd == "mysql")
            $this->verIdMysql();
        if ($this->motorbd == "pg")
            $this->verIdPg($tabla);
    }

    /**
     * Consulta la siguiente id de una tabla, tomada de la secuencia.
     */
    public function proxId($tabla)
    {
        $this->abrirConexionPg();
        $this->sql = "SELECT MAX(id)+1 as id FROM " . $tabla;
        $data = $this->ejecutarSentenciaPg(2);
        return $data[0]['id'];
    }

    public function ver_todo($condicion = false)
    {

        $this->abrirConexionPg();

        if (!$condicion)
        {
            $this->sql = "select  *  from " . $this->tabla . " where estatu=true ;";
        }
        else
        {
            $this->sql = "select  *  from " . $this->tabla . " where estatu=true " . $condicion . " ;";
        }

        $data = $this->ejecutarSentenciaPg(2);
        return $data;
    }

    public function ver_vista($vista, $condicion = '1=1')
    {
        $this->abrirConexionPg();
        $this->sql = "select  *  from " . $vista . " where  " . $condicion . ";";
        $data = $this->ejecutarSentenciaPg(2);
        return $data;
    }

    public function ver_uno($id, $campo = '')
    {
        $this->abrirConexionPg();
        if ($campo)
            $this->sql = "select  *  from " . $this->tabla . " where " . $campo . "='" . $id . "' and estatu=true";
        else
            $this->sql = "select  *  from " . $this->tabla . " where id='" . $id . "' and estatu=true";
        $data = $this->ejecutarSentenciaPg(2);
        return $data;
    }

    public function actualizar($tabla, $id, $campo, $valor)
    {
        $this->abrirConexionPg();
        $this->sql = "UPDATE  $tabla SET $campo='$valor'  WHERE id=$id";
        $data = $this->ejecutarSentenciaPg();
    }

    public function eliminar($campo = 'id', $tabla = FALSE)
    {
        $this->abrirConexionPg();
        if ($tabla)
            $this->tabla = $tabla;
        $this->sql = "UPDATE " . $this->tabla . " SET  estatu='FALSE'  WHERE $campo ='" . $_SESSION['id_eliminacion'] . "';";
        $data = $this->ejecutarSentenciaPg();
        #$this->historico($_SESSION['id_eliminacion'], 'eliminar'); OJO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        return $data;
    }

    /*  funciones para el correlativo o numero de documento */

    private function actual_correlativo_en_maestro()
    {
        $this->abrirConexionPg();
        $this->sql = "select  tipo1 as correlativo  from  maestro where id=" . $this->id_padre;
        $data = $this->ejecutarSentenciaPg(2);
        return $data[0]['correlativo'];
    }

    private function nuevo_correlativo()
    {
        $correlativo = $this->actual_correlativo_en_maestro();
        $this->correlativo = $correlativo + 1;
        $longitud = strlen($this->correlativo);
        $ceros = (5 - $longitud);
        $ceros = $this->cero_correlativo($ceros);

        $numero_documento = $this->numero_documento = $this->sigla . "-" . date('Y') . $ceros . $this->correlativo;
        $this->actualizar_correlativo_en_maestro();
        return $numero_documento;
    }

    private function cero_correlativo($total)
    {
        $res = '';
        for ($i = 1; $i <= $total; $i++)
        {
            $res.='0';
        }
        return $res;
    }

    private function actualizar_correlativo_en_maestro()
    {

        $this->abrirConexionPg();
        $this->sql = " UPDATE maestro SET tipo1='" . $this->correlativo . "' WHERE id=" . $this->id_padre;
        $data = $this->ejecutarSentenciaPg(2);
        return $data[0]['correlativo'];
    }

    public function alertas($msj)
    {
        echo "<script>alert('" . $msj . "') </script>";
    }

    public function encrypt($string)
    {
        /* $key = "lsdrojas@cluuf.com";
          $result = '';
          $string = base64_encode($string);
          for ($i = 0; $i < strlen($string); $i++) {
          $char = substr($string, $i, 1);
          $keychar = substr($key, ($i % strlen($key)) - 1, 1);
          $char = chr(ord($char) + ord($keychar));
          $result.=$char;
          return base64_encode($string);

          } */
        return base64_encode($string);
    }

    public function decrypt($string)
    {
        /* $key = "lsdrojas@cluuf.com";
          $result = '';
          $string = base64_decode($string);
          for ($i = 0; $i < strlen($string); $i++) {
          $char = substr($string, $i, 1);
          $keychar = substr($key, ($i % strlen($key)) - 1, 1);
          $char = chr(ord($char) - ord($keychar));
          $result.=$char;
          }
          return $result;
         */

        return base64_decode($string);
    }

    public function celda_u($label, $valor, $ancho, $ver = "si")
    {


        $clases['form_group'] = "form-group";
        $clases['form_group_label'] = " control-label label_text";
        $clases['form_group_valor'] = "label_text2";
        $clases['form_col'] = "col-group";


        if ($ver == "si")
        {

            echo "<div class=\"col-md-" . $ancho . "  " . $clases['form_col'] . " \">
            <div class='" . $clases['form_group'] . "' >
            <p>
            <span  class='" . $clases['form_group_label'] . "'  >" . $label . "</span>
            <span class='" . $clases['form_group_valor'] . "'    >" . $valor . "</span>
            </p>
            </div>
            </div>";
        }

        if ($ver == "no")
        {

            return "<div class=\"col-md-" . $ancho . "  " . $clases['form_col'] . " \">
            <div class='" . $clases['form_group'] . "' >
            <p>
            <span  class='" . $clases['form_group_label'] . "'  >" . $label . "</span>
            <span class='" . $clases['form_group_valor'] . "'    >" . $valor . "</span>
            </p>
            </div>
            </div>";
        }
    }

    public function celda_u_edit($tabla, $label, $campo, $valor, $ancho, $ver = "si")
    {


        $clases['form_group'] = "form-group";
        $clases['form_group_label'] = " control-label label_text";
        $clases['form_group_valor'] = "label_text2";
        $clases['form_col'] = "col-group";


        if ($ver == "si")
        {

            echo "<div class=\"col-md-" . $ancho . "  " . $clases['form_col'] . " \">
            <div class='" . $clases['form_group'] . "' >
            <p>
            <span  class='" . $clases['form_group_label'] . "'  >"
            . " <a href='#' onclick='update_data_" . $tabla . "(\"" . $campo . "\",\"" . $valor . "\")' ><i class='fa fa-edit' ></i></a>" . $label . "</span>


            <span class='" . $clases['form_group_valor'] . "'    >" . $valor . "</span>
            </p>
            </div>
            </div>";
        }

        if ($ver == "no")
        {

            return "<div class=\"col-md-" . $ancho . "  " . $clases['form_col'] . " \">
            <div class='" . $clases['form_group'] . "' >
            <p>
            <span  class='" . $clases['form_group_label'] . "'  >"
                    . " <a href='#' onclick='update_data_" . $tabla . "(\"" . $campo . "\",\"" . $valor . "\")' ><i class='fa fa-edit' ></i></a>" . $label . "</span>


            <span class='" . $clases['form_group_valor'] . "'    >" . $valor . "</span>
            </p>
            </div>
            </div>";
        }
    }

    public function row_u($ver = 'si')
    {


        if ($ver == "si")
        {
            echo "  <div class=\"row\">
            <div class=\"col-xs-12\">";
        }


        if ($ver == "no")
        {
            return " <div class=\"row\">
            <div class=\"col-xs-12\">";
        }
    }

    public function end_row_u()
    {

        if ($ver == "si")
        {
            echo "  </div>";
            echo "  </div>";
        }


        if ($ver == "no")
        {
            return "</div>  </div>";
        }
    }

    public function date_now()
    {
        return date('Y/m/d H:i:s');
    }

    /*  MODULO  MODAL  */

    public $modal_id = "";
    public $modal = "";
    public $modal_top = "";
    public $modal_row = "";
    public $modal_class = "";
    public $modal_bottom = "";
    public $modal_body_height = "600px";
    public $title_modal = " Titulo del Modal ";
    public $modal_data = "";
    public $modal_only_body = false;
    public $modal_data_new = false;
    public $modal_only_form = false;
    public $modal_form_name = false;
    public $modal_form_id = false;
    public $modal_form_class = "";
    public $modal_form_enctype_value = "enctype=\"multipart/form-data\" ";
    public $modal_form_enctype = false;
    public $modal_form_method = "POST";
    public $modal_form_action = "";
    public $modal_javascript = "";

    public function modal_reset()
    {
        $this->modal_id = "";
        $this->modal = "";
        $this->modal_top = "";
        $this->modal_row = "";
        $this->modal_bottom = "";
        $this->modal_body_height = "600px";
        $this->title_modal = " Titulo del Modal ";
        $this->modal_data = "";
        $this->modal_only_body = false;
        $this->modal_data_new = false;
        $this->modal_row = "";
        $this->modal_only_form = false;
        $this->modal_form_name = false;
        $this->modal_form_id = false;
        $this->modal_form_enctype = false;
        $this->modal_form_method = "POST";
        $this->modal_form_action = "";
        $this->modal_javascript = "";
    }

    public function modal_body_row_input($size, $campo, $label, $class, $type = "text")
    {


        if (!$this->modal_data_new)
        {

            if ($type == 'select')
            {

                $this->modal_row.= "<div class = \"col-md-" . $size . "\">
                    <div class = \"form-group\" >
                    <label class = \"control-label label_text\" >" . $label . "</label>
                    " . $this->modal_data[$campo] . "
                    </div>
                    </div>";
            }
            else
            {


                $this->modal_row.= "<div class = \"col-md-" . $size . "\">
                     <div class = \"form-group\" >
                     <label class = \"control-label label_text\" >" . $label . "</label>
                     <input  onclick='refresh_JS();' name=\"" . $campo . "\" value = \"" . $this->modal_data[$campo] . "\" class = \"input_text label_text2 " . $class . " \"  type = \"" . $type . "\" id = \"" . $campo . "\" >
                     </div>
                     </div>";
            }
        }



        if ($this->modal_data_new)
        {

            if ($type == 'select')
            {

                $this->modal_row.= "<div class = \"col-md-" . $size . "\">
                    <div class = \"form-group\" >
                    <label class = \"control-label label_text\" >" . $label . "</label>
                    " . $this->modal_data[$campo] . "
                    </div>
                    </div>";
            }
            else
            {



                $this->modal_row.= "<div class = \"col-md-" . $size . "\">
         <div class = \"form-group\" >
         <label class = \"control-label label_text\" >" . $label . "</label>
         <input  onclick='refresh_JS();' name=\"" . $campo . "\"  value = '' class = \"input_text label_text2\" " . $class . " type = \"text\" id = \"" . $campo . "\" >
         </div>
         </div>";
            }
        }
    }

    public function modal_body_row_input_json($data_json)
    {

        if (!$this->modal_data_new)
        {


            if ($data_json['edit'] == 'false')
            {

            }
            else

            if ($data_json['type'] == 'select')
            {

                $this->modal_row.= "<div class = \"col-md-" . $data_json['size'] . "\">
                    <div class = \"form-group\" >
                    <label class = \"control-label label_text\" >" . $data_json['label'] . "</label>
                    " . $this->modal_data[$data_json['campo']] . "
                    </div>
                    </div>";
            }
            else if ($data_json['type'] == 'file')
            {

                $this->modal_row.= "
<div class = \"col-md-" . $data_json['size'] . "\">
<div class = \"form-group\" >
<label class = \"control-label label_text\" >" . $data_json['label'] . "</label>
<input   onclick='refresh_JS();'  style=\" " . $data_json['style'] . " \"  name=\"" . $data_json['campo'] . "\" value = \"" . $this->modal_data[$data_json['campo']] . "\" class = \"input_text label_text2 " . $data_json['class'] . " \"  type = \"" . $data_json['type'] . "\" id = \"" . $data_json['campo'] . "\"  " . $data_json['externo'] . "  >
<input type=\"hidden\"   name=\"MAX_FILE_SIZE\" value=\"" . $data_json['maxfilesize'] . "\" />
    <input type=\"hidden\"   name=\"url\" value=\"" . $data_json['url'] . "\" />
</div></div>";
            }
            else if ($data_json['type'] == 'readonly')
            {

                $this->modal_row.= "<div class = \"col-md-" . $data_json['size'] . " " . $data_json['class_modal_row'] . "   \" style=\" " . $data_json['style_modal_row'] . " \"  >
                    <div class = \"form-group\" >
                    <label class = \"control-label label_text\" >" . $data_json['label'] . "</label>
                    <div  style=\" " . $data_json['style'] . " \"  class=" . $data_json['class'] . " >" . $this->modal_data[$data_json['campo']] . "</div>
                    </div>
                    </div>";
            }
            else
            {


                $this->modal_row.= "<div class = \"col-md-" . $data_json['size'] . " " . $data_json['class_modal_row'] . " \" style=\" " . $data_json['style_modal_row'] . " \"   >
                     <div class = \"form-group\" >
                     <label class = \"control-label label_text\" >" . $data_json['label'] . "</label>
                     <input  onclick='refresh_JS();'  style=\" " . $data_json['style'] . " \"  name=\"" . $data_json['campo'] . "\" value = \"" . $this->modal_data[$data_json['campo']] . "\" class = \"input_text label_text2 " . $data_json['class'] . " \"  type = \"" . $data_json['type'] . "\" id = \"" . $data_json['campo'] . "\"  " . $data_json['externo'] . "  >
                     </div>
                     </div>";
            }
        }


        if ($this->modal_data_new)
        {

            if ($data_json['type'] == 'select')
            {

                $this->modal_row.= "<div class = \"col-md-" . $data_json['size'] . " " . $data_json['class_modal_row'] . " \" style=\" " . $data_json['style_modal_row'] . " \" >
                    <div class = \"form-group\" >
                    <label class = \"control-label label_text\" >" . $data_json['label'] . "</label>
                     " . $this->modal_data[$data_json['campo']] . "
                    </div>
                    </div>";
            }
            else
            {



                $this->modal_row.= "<div class = \"col-md-" . $data_json['size'] . "  " . $data_json['class_modal_row'] . " \" style=\" " . $data_json['style_modal_row'] . " \" >
         <div class = \"form-group\" >
         <label class = \"control-label label_text\" >" . $data_json['label'] . "</label>
         <input  onclick='refresh_JS();' style=\" " . $data_json['style'] . " \" name=\"" . $data_json['campo'] . "\"  value = ''  class = \"input_text label_text2 " . $data_json['class'] . " \" type = \"" . $data_json['type'] . "\"  id = \"" . $data_json['campo'] . "\"  " . $data_json['externo'] . "  >
         </div>
         </div>";
            }
        }
    }

    public function modal_body_row_select($size, $campo, $label, $class = "")
    {

        $this->modal_row.= "<div class = \"col-md-" . $size . "\">
    <div class = \"form-group\" >
    <label class = \"control-label label_text\" >" . $label . "</label>
    " . $this->modal_data[$campo] . "
    </div>
    </div>";
    }

    public function modal_body()
    {

        return "<div class=\"modal-body\" style=\"height:" . $this->modal_body_height . "\">" . $this->modal_row . "</div>";
    }

    public function modal_header()
    {
        return "	<div class=\"modal-header\">
    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>
    <h3 class=\"smaller lighter blue no-margin\">" . $this->title_modal . "</h3>
    </div>";
    }

    public function modal_footer_buttons($nombre, $title, $color = "success")
    {

        $this->modal_bottom.="<button  onclick='" . $nombre . "_click()' name='" . $nombre . "' class=\"btn btn-sm btn-" . $color . "  pull-right\" data-dismiss=\"modal\">
    <i class=\"ace-icon fa fa-check\"></i>
    " . $title . "
    </button>";
    }

    public function modal_footer_buttons_json($json)
    {

        $this->modal_bottom.="<a  style=\"" . $json['style'] . "\" id=\"" . $json['id'] . "\" onclick=\"" . $json['onclick'] . " \"  name=\"" . $json['name'] . "\"  class=\"btn btn-sm btn-" . $json['color'] . "  pull-right\" data-dismiss=\"" . $json['data-dismiss'] . "\">
    <i class=\"ace-icon fa fa-check\"></i>
    " . $json['title'] . "
    </a>";
    }

    public function modal_footer_buttons_confirm($nombre, $title, $color = "success")
    {

        $this->modal_bottom.="<a  class=\"btn btn-sm btn-" . $color . "  pull-right\" data-dismiss=\"modal\" ><i class=\"ace-icon fa fa-check\"></i>

    " . $title . "
    </a>";
    }

    public function modal_footer()
    {


        return "<div class=\"modal-footer\">
    " . $this->modal_bottom . "
    <button class=\"btn btn-sm btn-danger pull-right\" data-dismiss=\"modal\">
    <i class=\"ace-icon fa fa-times\"></i>
    Close
    </button>

    </div>";
    }

    public function modal_form_content()
    {


        if (!$this->modal_form_enctype)
        {
            $this->modal_form_enctype_value = "";
        }


        if ($this->modal_only_form)
        {

            return "<form action=\"" . $this->modal_form_action . "\"  method=\"" . $this->modal_form_method . "\" " . $this->modal_form_enctype_value . "  onmouseover=\"refresh_JS()\"  class=\"" . $this->modal_form_class . "\"  name=\"" . $this->modal_form_name . "\"  id=\"" . $this->modal_form_id . "\"  ><input name='cluuf 'type='hidden' value='CLUUF'><div id=\"" . $this->modal_id . "\" ><div class=\"modal-dialog\"><div class=\"modal-content\">"
                    . "" . $this->modal_header() . $this->modal_body() . $this->modal_footer() . "</div></div></div></form>" . $this->modal_javascript;
        }
        else if (!$this->modal_only_body)
        {

            return "<form   action=\"" . $this->modal_form_action . "\"  method=\"" . $this->modal_form_method . "\" " . $this->modal_form_enctype_value . " onmouseover=\"refresh_JS()\"  class=\"" . $this->modal_form_class . "\" name=\"" . $this->modal_form_name . "\"  id=\"" . $this->modal_form_id . "\"  ><input name='cluuf 'type='hidden' value='CLUUF'><div id=\"" . $this->modal_id . "\" class=\"modal fade\" tabindex=\"-1\"><div class=\"modal-dialog\"><div class=\"modal-content\">"
                    . "" . $this->modal_header() . $this->modal_body() . $this->modal_footer() . "</div></div></div></form>" . $this->modal_javascript;
        }
        else if ($this->modal_only_body)
        {

            return "<form  action=\"" . $this->modal_form_action . "\"  method=\"" . $this->modal_form_method . "\" " . $this->modal_form_enctype_value . "  onmouseover=\"refresh_JS()\"  class=\"" . $this->modal_form_class . "\" name=\"" . $this->modal_form_name . "\"  id=\"" . $this->modal_form_id . "\"  ><input name='cluuf 'type='hidden' value='CLUUF'><div id=\"" . $this->modal_id . "\" class=\"modal fade\" tabindex=\"-1\"><div class=\"modal-dialog\"><div class=\"modal-content\">"
                    . "" . $this->modal_body() . $this->modal_footer() . "</div></div></div></form>" . $this->modal_javascript;
        }
    }

    public function modal_content()
    {


        if ($this->modal_only_form)
        {

            return "<div   class=\"" . $this->modal_class . "\" id=\"" . $this->modal_id . "\" ><div class=\"modal-dialog\"><div class=\"modal-content\">"
                    . "" . $this->modal_header() . $this->modal_body() . $this->modal_footer() . "</div></div></div>" . $this->modal_javascript;
        }
        else if (!$this->modal_only_body)
        {

            return "<div id=\"" . $this->modal_id . "\" class=\"modal fade  " . $this->modal_class . "\" tabindex=\"-1\"><div class=\"modal-dialog\"><div class=\"modal-content\">"
                    . "" . $this->modal_header() . $this->modal_body() . $this->modal_footer() . "</div></div></div>" . $this->modal_javascript;
        }
        else if ($this->modal_only_body)
        {

            return "<div id=\"" . $this->modal_id . "\" class=\"modal fade  " . $this->modal_class . "\" tabindex=\"-1\"><div class=\"modal-dialog\"><div class=\"modal-content\">"
                    . "" . $this->modal_body() . $this->modal_footer() . "</div></div></div>" . $this->modal_javascript;
        }
    }

    /*  MODULO  VIEW  */

    public $view_row = "";
    public $view_clase_form_col = "col-group";
    public $view_clase_form_group = "form-group";
    public $view_clase_form_group_label = " control-label label_text ";
    public $view_clase_form_group_valor = "  label_text2 ";
    public $view_display = false;
    public $view_data = "";

    public function VIEW_body_row($campo, $label, $ancho = '6')
    {

        $this->view_row.= "
    <div class=\"col-md-" . $ancho . "  " . $this->view_clase_form_col . " \">
    <div class='" . $this->view_clase_form_group . "' >
    <p>
    <span  class='" . $this->view_clase_form_group_label . "'  >" . $label . "</span>
    <span class='" . $this->view_clase_form_group_valor . "'    >" . $this->view_data[$campo] . "</span>
    </p>
    </div>
    </div>";
    }

    public function VIEW_body()
    {

        return "  <div class=\"row\">
    <div class=\"col-xs-12\">" . $this->view_row . "</div>"
                . "</div>";
    }

    public function VIEW_content()
    {

        if ($this->view_display)
        {
            echo $this->VIEW_body();
        }

        if (!$this->view_display)
        {
            return $this->VIEW_body();
        }
    }

    /*   FIN VIEW  */



    /*  MODULO  VIEW  */

    public $pdf_row = "";
    public $pdf_clase_form_col = "col-group";
    public $pdf_clase_form_group = "form-group";
    public $pdf_clase_form_group_label = " control-label label_text ";
    public $pdf_clase_form_group_valor = "  label_text2 ";
    public $pdf_display = false;
    public $pdf_data = "";

    public function PDF_body_row($campo, $label, $ancho = '6')
    {

        $this->pdf_row.= "<span><b>" . $label . ":</b></span><span>&nbsp;&nbsp;" . $this->pdf_data[$campo] . "</span><br>";
    }

    public function PDF_body()
    {

        return $this->pdf_row;
    }

    public function PDF_content()
    {

        if ($this->pdf_display)
        {
            echo $this->PDF_body();
        }

        if (!$this->pdf_display)
        {
            return $this->PDF_body();
        }
    }

    /*   FIN VIEW  */

    public $datatable_struct = "";
    public $datatable_data = "";
    public $datatable_th = "";
    public $datatable_tr = "";

    public function DATATABLE_edit()
    {


        for ($i = 0; $i < count($this->datatable_struct['edit']); $i++)
        {

            $this->datatable_th.= "<th></th>";
            $this->datatable_tr.= "<td><a href='" . $this->datatable_struct['edit'][$i]['href'] . $this->encrypt($this->datatable_struct['edit'][$i]['href']) . "'>" . $this->datatable_struct['edit'][$i]['icon'] . "</a></td>";
        }
    }

    public function DATATABLE_head()
    {

        $this->datatable_th.="<thead><tr>";


        for ($i = 0; $i < count($this->datatable_struct['th']); $i++)
        {

            if ($this->datatable_struct['tr'][$i]['visible'] == 'true')
            {
                $this->datatable_th.= "<th class=" . $this->datatable_struct['tr'][$i]['class'] . " >" . $this->datatable_struct['th'][$i]['value'] . "</th>";
            }
        }

        $this->datatable_th.="</tr></thead>";

        return $this->datatable_th;
    }

    public function DATATABLE_body()
    {


        $this->datatable_tr.="<tbody>";


        for ($y = 0; $y < count($this->datatable_data); $y++)
        {




            $this->datatable_tr.="<tr   >";



            for ($i = 0; $i < count($this->datatable_struct['tr']); $i++)
            {




                if ($this->datatable_struct['tr'][$i]['edit'])
                {

                    $this->datatable_tr.= "<td  class=" . $this->datatable_struct['tr'][$i]['class'] . " style=\"width:" . $this->datatable_struct['tr'][$i]['width'] . " \"> ";

                    for ($z = 0; $z < count($this->datatable_struct['tr'][$i]['edit']); $z++)
                    {

                        if ($this->datatable_struct['tr'][$i]['edit'][$z]['mode']=='href')
                        {
                            $this->datatable_tr.= "<a  href=\"" . $this->datatable_struct['tr'][$i]['edit'][$z]['href']."'".$this->encrypt($this->datatable_data[$y][$this->datatable_struct['tr'][$i]['edit'][$z]['parametro']])."\"    onclick=\" ".$this->datatable_struct['tr'][$i]['edit'][$z]['funcion']."\">" . $this->datatable_struct['tr'][$i]['edit'][$z]['icon'] . "</a>&nbsp;";
                        }
                        else
                        {
                            $this->datatable_tr.= "<a  href=\"" . $this->datatable_struct['tr'][$i]['edit'][$z]['href'] . "\" role=\"button\"  data-toggle=\"modal\"  onclick=\"  " . $this->datatable_struct['tr'][$i]['edit'][$z]['funcion'] . "('" . $this->encrypt($this->datatable_data[$y][$this->datatable_struct['tr'][$i]['edit'][$z]['parametro']]) . "')\">" . $this->datatable_struct['tr'][$i]['edit'][$z]['icon'] . "</a>&nbsp;";
                        }
                    }
                    $this->datatable_tr.= "</td>";
                }
                else
                {
                    if ($this->datatable_struct['tr'][$i]['visible'] == 'true')
                    {
                        $this->datatable_tr.= "<td  class=" . $this->datatable_struct['tr'][$i]['class'] . " >" . $this->datatable_data[$y][$this->datatable_struct['tr'][$i]['value']] . "</td>";
                    }
                }
            }

            $this->datatable_tr.="</tr>";
        }

        return $this->datatable_tr.="</tbody>";
    }

    public function DATATABLE_content()
    {

        return "<table  style=\"" . $this->datatable_struct['style'] . "\"  id=\"" . $this->datatable_struct['id'] . "\"  class=\"table table-striped table-bordered table-hover datatable\">" . $this->DATATABLE_head() . $this->DATATABLE_body() . "</table>";
    }

    public function FORMAT_is_date_complete($fecha)
    {

        return $fecha;
    }

    public $save_campo = "";
    public $save_value = "";
    public $save_campo_where = "";
    public $save_value_where = "";
    public $save_tabla = "";

    public function SAVE__()
    {


        for ($i = 0; $i < count($this->save_campo); $i++)
        {

            $limitador = "  ";

            if ((count($this->save_campo) - 1) > $i)
            {

                $limitador = ",";
            }

            $campo_sql.= $this->save_campo[$i] . $limitador;

            $value_sql.= "'" . $this->save_value[$i] . "'" . $limitador;



            $campo_sql_update.= $this->save_campo[$i] . " = ";

            $campo_sql_update.= "'" . $this->save_value[$i] . "'" . $limitador;
        }



        for ($z = 0; $z < count($this->save_campo_where); $z++)
        {

            $where_sql_update.= " " . $this->save_campo_where[$z] . " in (";
            $where_sql_update.= "'" . $this->save_value_where[$z] . "') and";
        }


        $campo_sql = substr($campo_sql, 0, -1);
        $value_sql = substr($value_sql, 0, -1);
        $campo_sql_update = substr($campo_sql_update, 0, -1);
        $where_sql_update = substr($where_sql_update, 0, -3);


        if (strlen($where_sql_update) > 10)
        {
            $sql = "UPDATE " . $this->save_tabla . " SET " . $campo_sql_update . " WHERE " . $where_sql_update . ";";
            $data = $this->_update_sql($sql);
        }
        else
        {

            if (substr($campo_sql, -1, 1) == ',')
            {
                $campo_sql = substr($campo_sql, 0, -1);
                $value_sql = substr($value_sql, 0, -1);
            }


            $sql = "INSERT INTO " . $this->save_tabla . " (" . $campo_sql . ") VALUES (" . $value_sql . ")";
            $data = $this->_update_sql($sql);

            return $this->ultimoId;
        }
    }

    public $select_vista = "";
    public $select_select_all = "";

    public function SELECT__($condicion)
    {


        if ($condicion == '0')
        {

            $sql = "SELECT * FROM " . $this->select_vista . " ;";
            $data = $this->ejecutar_query($sql);
            $data = $this->DATAPRINT__($data);
        }
        else if ($condicion == '1')
        {

            $sql = "SELECT * FROM " . $this->select_vista . " WHERE  " . $this->select_select_all['campo'] . "  in (" . $this->select_select_all['id'] . ");";
            $data = $this->ejecutar_query($sql);
            $data = $this->DATAPRINT__($data);
            $data = $data[0];
        }
        else
        {

             $sql = "SELECT * FROM " . $this->select_vista . " WHERE  " . $condicion . ";";
            $data = $this->ejecutar_query($sql);
            $data = $this->DATAPRINT__($data);
        }

        return $data;
    }

    public $dataprint_data_json = "";

    public function DATAPRINT__($data)
    {



        $data_json = $this->dataprint_data_json;


        for ($i = 0; $i < count($data); $i++)
        {


            for ($y = 0; $y < count($data_json['type_pk']); $y++)
            {
                $data[$i][$data_json['type_pk'][$y]['campo'] . "_code"] = $this->DATAPRINT__is_encrypt($data[$i][$data_json['type_pk'][$y]['campo']]);
            }



            for ($y = 0; $y < count($data_json['type_date']); $y++)
            {
                $data[$i][$data_json['type_date'][$y]['campo']] = $this->DATAPRINT__is_date_print($data[$i][$data_json['type_date'][$y]['campo']]);
            }



            for ($y = 0; $y < count($data_json['type_moneda']); $y++)
            {

                $data[$i][$data_json['type_moneda'][$y]['campo'] . "_format"] = $this->DATAPRINT__is_formato_moneda_($data[$i][$data_json['type_moneda'][$y]['campo']]);

                $data[$i][$data_json['type_moneda'][$y]['campo'] . "_format_type"] = $data[$i][$data_json['type_moneda'][$y]['campo'] . "_format"] . " " . $data_json['type_moneda'][$y]['type'];
            }

            for ($y = 0; $y < count($data_json['type_combo_fijo']); $y++)
            {

                $data[$i][$data_json['type_combo_fijo'][$y]['campo'] . "_combo_fijo"] = $this->DATAPRINT__is_combo_fijo(
                        $data[$i][$data_json['type_combo_fijo'][$y]['campo']], $data_json['type_combo_fijo'][$y]['option'], $data_json['type_combo_fijo'][$y]['campo']
                );


                $data[$i][$data_json['type_combo_fijo'][$y]['campo'] . "_fk_value_fijo"] = $this->DATAPRINT__fk_value_fijo(
                        $data[$i][$data_json['type_combo_fijo'][$y]['campo']], $data_json['type_combo_fijo'][$y]['option'], $data_json['type_combo_fijo'][$y]['campo']
                );
            }


            for ($y = 0; $y < count($data_json['type_combo']); $y++)
            {

                $data[$i][$data_json['type_combo'][$y]['fk_id'] . "_combo"] =
                $this->DATAPRINT__is_combo_(
                        $data[$i][$data_json['type_combo'][$y]['fk_id']],$data_json['type_combo'][$y]);


                $data[$i][$data_json['type_combo'][$y]['fk_id'] . "_fk"] = $this->DATAPRINT__fk_value(
                        $data[$i][$data_json['type_combo'][$y]['fk_id']], $data_json['type_combo'][$y]['fk_id'], $data_json['type_combo'][$y]['fk_table'], $data_json['type_combo'][$y]['fk_table_id'], $data_json['type_combo'][$y]['fk_table_campo'], $data_json['type_combo'][$y]['fk_table_where']
                );
            }


            for ($y = 0; $y < count($data_json['type_img']); $y++)
            {

                $data[$i][$data_json['type_img'][$y]['campo'] . "_format_img"] = $this->DATAPRINT__is_img($data[$i][$data_json['type_img'][$y]['campo']], $data_json['type_img'][$y]);

                $data[$i][$data_json['type_img'][$y]['campo'] . "_format_img_url"] = $this->DATAPRINT__is_img_url($data[$i][$data_json['type_img'][$y]['campo']], $data_json['type_img'][$y]);
            }


            for ($y = 0; $y < count($data_json['type_costototal']); $y++)
            {

                $data[$i][$data_json['type_costototal'][$y]['campo'] . "_costototal"] = $this->DATAPRINT__is_costototal($data[$i][$data_json['type_costototal'][$y]['campo']], $data_json['type_costototal'][$y]);


                #     $data[$i][$data_json['type_costototal'][$y]['campo'] . "_costototal_format"] = $this->DATAPRINT__is_formato_moneda_($data[$i][$data_json['type_costototal'][$y]['campo'] . "_costototal"]);
                #    $data[$i][$data_json['type_costototal'][$y]['campo'] . "_costototal_format_type"] = $data[$i][$data_json['type_costototal'][$y]['campo'] . "_format"] . " " . $data_json['type_costototal'][$y]['type'];
            }
        }


        //$data = $this->formatos_especiales($data);


        return $data;
    }

    public function DATAPRINT__is_date_print($fecha)
    {
        $yy = substr($fecha, 0, 4);
        $mm = substr($fecha, 5, 2);
        $dd = substr($fecha, 8, 2);
        $fecha = $dd . "/" . $mm . "/" . $yy;
        return $fecha;
    }

    public function DATAPRINT__is_numeric_($n)
    {

        if (is_numeric($n))
            return $n;
        else
            return "";
    }

    public function DATAPRINT__is_costototal($n)
    {

        return ($n * 1000 );
    }

    public function DATAPRINT__is_formato_moneda_($n)
    {
        return $n = number_format($n, 2, ",", ".");
    }

    public function DATAPRINT__is_date($fecha = "")
    {

        if (strlen($fecha) < 4)
        {
            $fecha = date('Y-m-d H:i:s');
        }
        else
        {

            $dd = substr($fecha, 0, 2);
            $mm = substr($fecha, 3, 2);
            $yy = substr($fecha, 6, 4);
            $fecha = $yy . "/" . $mm . "/" . $dd;
        }

        return $fecha;
    }

    public function DATAPRINT__is_encrypt($x)
    {
        return $this->encrypt($x);
    }

    public function DATAPRINT__is_decrypt($x)
    {
        return $this->decrypt($x);
    }

    public function DATAPRINT__is_md5($x)
    {
        return md5($x);
    }

    public function DATAPRINT__is_img($x, $json)
    {

        #   return "<img  src='".$x."'  >";


        return "<img  src='" . $json['url'] . $x . "'  width='" . $json['width'] . "'  class='" . $json['class'] . "'   style='" . $json['style'] . "' >";
    }

    public function DATAPRINT__is_img_url($x, $json)
    {
        return $json['url'] . $x;
    }

    public function DATAPRINT__is_sec01()
    {
        $obj = new padreModelo();
        $sql = "select (id+1) as sec from " . $this->tabla . " order by 1 desc limit 1 ";
        $data = $obj->ejecutar_query($sql);
        return 'MMSV' . date('Ym') . $data[0]['sec'];
    }

    public function DATAPRINT__is_delete($x)
    {
        if (strlen($x) < 1)
        {
            return 't';
        }
        else
        {
            return $x;
        };
    }

    public function DATAPRINT__is_format_this($x)
    {
        return ucwords(strtolower($x));
    }

    public function DATAPRINT__is_combo_($valor, $data_json)
    {



        $sal.="<select id='" . $data_json['fk_id'] . "' name='" . $data_json['fk_id'] . "' class='form-control " . $data_json['class'] . "' >";
        $obj = new padreModelo();
        $sql = "select * from " . $data_json['fk_table'] . " where  $" . $data_json['fk_table_where'] . "   ";


        $data = $obj->ejecutar_query($sql);



        foreach ($data as $dato)
        {

            if ($valor == $dato[$data_json['tabla_id']])
            {
                $sal.="<option value='" . $data_json['tabla_id'] . "' selected >" . $this->DATAPRINT__is_format_this($dato[$data_json['fk_table_campo']]) . "</option>";
            }
            else
            {
                $sal.="<option value='" . $data_json['tabla_id'] . "'>" . $this->DATAPRINT__is_format_this($dato[$data_json['fk_table_campo']]) . "</option>";
            }
        }

        $sal.="</select>";

        return $sal;
    }

    public function DATAPRINT__fk_value($valor, $campo, $tabla, $tabla_id, $tabla_campo, $condicion)
    {

        $obj = new padreModelo();
        $sql = "select " . $tabla_campo . " as id  from " . $tabla . " where  " . $tabla_id . " = '" . $valor . "'   ";
        $data = $obj->ejecutar_query($sql);
        return $this->DATAPRINT__is_format_this($data[0]['id']);
    }

    public function DATAPRINT__fk_value_fijo($valor, $data, $campo)
    {

        foreach ($data as $dato)
        {

            if ($valor == $dato['value'])
            {
                $sal.=$this->DATAPRINT__is_format_this($dato['name']);
            }
        }

        return $sal;
    }

    public function DATAPRINT__is_combo_fijo($valor, $data, $campo)
    {


        $sal.="<select id='" . $campo . "' name='" . $campo . "' class='form-control' >";


        foreach ($data as $dato)
        {

            if ($valor == $dato['value'])
            {
                $sal.="<option value='" . $dato['value'] . "' selected >" . $this->DATAPRINT__is_format_this($dato['name']) . "</option>";
            }
            else
            {
                $sal.="<option value='" . $dato['value'] . "'>" . $this->DATAPRINT__is_format_this($dato['name']) . "</option>";
            }
        }

        return $sal.="</select>";
    }

    public function FIXTYPE__($nombre, $valor, $data_json)
    {


        if (strlen($valor) < 1)
        {

            $defecto = $data_json;
            $valor = $defecto[$nombre];
        }


        foreach ($data_json['type_date'] as &$value)
        {

            if ($value['campo'] == $nombre)
            {
                $valor = $this->DATAPRINT__is_date($valor);
                break;
            }
        }

        foreach ($data_json['type_numeric'] as &$value)
        {
            if ($value['campo'] == $nombre)
            {
                $valor = $this->DATAPRINT__is_numeric_($valor);
                break;
            }
        }

        foreach ($data_json['type_sec'] as &$value)
        {
            if ($value['campo'] == $nombre)
            {
                $sql = "select " . $value['alias'] . " as sec from " . $value['table'] . " order by 1 desc limit 1 ";
                $data = $this->ejecutar_query($sql);
                $valor = $value['part1'] . date('Ym') . $data[0]['sec'];

                break;
            }
        }

        foreach ($data_json['type_md5'] as &$value)
        {
            if ($value['campo'] == $nombre)
            {
                $valor = $this->DATAPRINT__is_md5($valor);
                break;
            }
        }


        foreach ($data_json['type_delete'] as &$value)
        {
            if ($value['campo'] == $nombre)
            {
                $valor = $this->DATAPRINT__is_delete($valor);
                break;
            }
        }

        return $valor;
    }

}

$objeto = new padreModelo();
#$objeto->cargos_automaticos_reservaciones();
?>
