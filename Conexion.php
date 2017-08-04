<?php
class Conexion {

    public $conexPg;
    public $conexLdap;
    public $conexSpi;
    /*

      private $port           = "5432";
      private $dbname         = "mmsv";
      private $host           = "127.4.112.2";
      private $user           = "adminhddsuws";
      private $password       = "H35im7NVdsmI";
     */
    private $port = "5432";
    private $dbname = "skydinners";
    private $host = "localhost";
    private $user = "postgres";
    private $password = "postgres";
    private $mysql_user = "root";
    private $mysql_pass = "12345789";
    private $mysql_host = "localhost";
    public $mysql_bd = "ibuhotelDB";
    private $mysql_port = "3306";
    public $conexMysql;
    public $motorbd = "mysql";

    /*
      private $port = "5432";
      private $dbname = "estilist_home";
      private $host = "localhost";
      private $user = "estilist_root";
      private $password = "mmsv.2015";

      private $mysql_user = "root";
      private $mysql_pass = "123456789";
      private $mysql_host = "localhost";
      private $mysql_bd = "noticias";
      private $mysql_port = "3306";
      public $conexMysql;
     */

    /**
     *  Funcion para conectar con la base de datos PostgreSQL
     */
    function abrirConexionPg($tipo = "admin") {
        $cadconex = "host=" . $this->host . " port=" . $this->port . " dbname=" . $this->dbname . "  user=" . $this->user . " password=" . $this->password;
        $result = pg_connect($cadconex);
        if ($result) {
            $this->conexPg = $result;
        } else {
            echo "Error consulta de Conexion Base de Datos PG";
            return false;
        }
    }

    /**
     *  Funcion para conectar con Mysql
     */

    /**
     *  Ejecuta las consultas a la base de datos
     * @param String $sql
     * @param Integer $modo 1:Insert, Update, Delete - 2:Select
     * @return array
     */
    function ejecutarSentenciaPg($modo = 1) {
        $this->abrirConexionPg();
        $result = pg_query($this->conexPg, $this->sql);
        if ($modo == 1) {
            return $result;
        } else if ($modo == 2) {
            if (!$result) {
                echo "<br>" . $this->sql . "<br>" . "Error Query" . "<br>";
                $rows = false;
            } else {
                $rows = pg_fetch_all($result);
            }
            return $rows;
        }
    }

    function ejecutarSentenciaPg01($modo = 1) {
        $result = pg_query($this->conexPg, $this->sql);
        if ($modo == 1) {
            return $result;
        } else if ($modo == 2) {
            if (!$result) {
                echo "<br>" . $this->sql . "<br>" . "Error Query" . "<br>";
                $rows = false;
            } else {
                $rows = pg_fetch_object($result);
            }
            return $rows;
        }
    }

    /**
     *  Funcion para cerrar la conexion con la base de datos Postgres
     */
    function cerrarConexionPg() {
        pg_close($this->conexPg);
    }

    function convertir_fecha($fecha) {
        $ano = substr($fecha, -4);
        $mes = substr($fecha, -7, 2);
        $dia = substr($fecha, -10, 2);
        return "$ano/$mes/$dia";
    }

    private function direccionar($modulo, $archivo = "") {
        echo "<script>location.href='../../modulo/" . $modulo . "/" . $archivo . "';</script>";
    }

    public function acceso($acceso, $nivel = 0) {
        $validador = false;
        for ($i = 0; $i <= count($_SESSION['acceso']); $i++) {
            if (isset($_SESSION['acceso'][$i]) == $acceso) {
                $validador = true;
            }
        }
        if ($validador == false) {
            #$this->direccionar('inicio');
        }
    }

    public function historico($op, $tb) {
        $u = $_SESSION['id_usuario'];
        $this->abrirConexionPg();
        $this->sql = " INSERT INTO fais.historico (operacion,tabla,usuario,registro)
                            VALUES ( '$op', '$tb', '$u' ,NOW() ); ";
        $data = $this->ejecutarSentencia();
        return $data;
    }

    /**
     *  Funcion para conectar con LDAP
     */
    function abrirConexionLdap() {
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
        define('DS2', '\\');
        define('BaseDn', 'DC=SIDETUR,DC=CORP');

        $this->conexLdap = ldap_connect("ldaps://10.2.50.1:636", 636) or die("Error Conexión ldap");
        ldap_set_option($this->conexLdap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->conexLdap, LDAP_OPT_REFERRALS, 0);
    }

    function logearLdap($login, $contrasena) {
        $bind = @ldap_bind($this->conexLdap, "SIDETUR" . DS2 . $login, $contrasena); # or die("Error Autenticación Ldap" . ldap_errno($this->conexLdap));
        return $bind;
    }

    function buscarLdap($login) {
        $query = ldap_search($this->conexLdap, BaseDn, "samaccountname=" . $login);
        $data = ldap_get_entries($this->conexLdap, $query);
        return $data;
    }

    function cambiarClaveLdap($login, $clave) {
        $query = ldap_search($this->conexLdap, BaseDn, "samaccountname=" . $login);
        $entry = ldap_first_entry($this->conexLdap, $query);
        $userDn = ldap_get_dn($this->conexLdap, $entry);
        $userdata["unicodePwd"] = mb_convert_encoding("\"" . $clave . "\"", "UTF-16LE");
        $data = ldap_mod_replace($this->conexLdap, $userDn, $userdata) or die("Error al cambiar la contraseña. Contacte al administrador de Sistema" . ldap_error($this->conexLdap));
        return $data;
    }

    function cerrarConexionLdap() {
        ldap_close($this->conexLdap);
    }

    /**
     *  Funcion para conectar con SPI (Oracle 9i)
     */
    function abrirConexionSpi() {
        $cadconex = oci_connect('DGISOPORTE', 'complejo', '10.2.1.35/spi', 'utf8');
        if (!$cadconex) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        } else
            $this->conexSpi = $cadconex;
    }

    /**
     *  Ejecuta las consultas a la base de datos SPI Oracle(9i)
     * @param String $sql
     * @param Integer $modo 1:Insert, Update, Delete - 2:Select
     * @return array
     */
    function ejecutarSentenciaSpi($modo = 1) {

        $result = oci_parse($this->conexSpi, $this->sql);
        oci_execute($result);
        if ($modo == 1) {
            return $result;
        } else if ($modo == 2) {
            if (!$result) {
                echo "<br>" . $this->sql . "<br>" . "Error Query" . "<br>";
                $rows = false;
            } else {
                oci_fetch_all($result, $rows, null, null, OCI_FETCHSTATEMENT_BY_ROW);
            }
            return $rows;
        }
    }

    public function add_dataPg($campo, $valor, $strtoupper = TRUE) {


        strlen($valor) <= 0 ? $valor = 'null , ' : $valor = "'" . trim(pg_escape_string($strtoupper ? strtoupper(strtr($valor, "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ")) : $valor)) . "', ";


        if (isset($this->nuevo)) {//Nuevo Registro
            $this->sql_campo.=$campo . ',';
            $this->sql_valor.=$valor;
        } else {//Actualización de Registro
            $this->sql_valor.= $campo . " = " . $valor;
        }
    }

    public function ejecutarPg($tipo_id = 'id') {

        $this->abrirConexionPg();
        if (isset($this->nuevo)) {//Nuevo Registro
            $this->sql = " INSERT INTO " . $this->tabla . " (" . $this->sql_campo . "registro,estatu) VALUES (" . $this->sql_valor . "'now()','" . $this->estatu . "'); ";
        } else {//Actualización de Registro
            $this->sql = "UPDATE  " . $this->tabla . "  SET  " . substr($this->sql_valor, 0, -2);
            $this->sql.= " WHERE " . $tipo_id . " in (" . $this->id . ")";
        }
        $this->ejecutarSentenciaPg(2);
        if (isset($this->nuevo))//Nuevo Registro
            $this->id = $this->verId($this->tabla);
        unset($this->nuevo, $this->sql_campo, $this->sql_valor);
    }

    public function verIdPg($tabla) {
        $this->abrirConexionPg();
        $this->sql = "SELECT CURRVAL('" . $tabla . "_id_seq') as id";
        $data = $this->ejecutarSentenciaPg(2);
        return $data[0]['id'];
    }

    public function add_dataMysql($campo, $valor, $strtoupper = TRUE) {

        strlen($valor) <= 0 ? $valor = 'null , ' : $valor = "'" . trim(pg_escape_string($strtoupper ? strtoupper(strtr($valor, "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ")) : $valor)) . "', ";

        if (isset($this->nuevo)) {
            $this->sql_campo.=$campo . ',';
            $this->sql_valor.=$valor;
        } else {
            $this->sql_valor.= $campo . "=" . $valor . "";
        }
    }

    public function ejecutarMysql($tipo_id = 'id') {

        $this->abrirConexionMysql();

        if (isset($this->nuevo)) {
            $this->sql_valor = substr(trim($this->sql_valor), 0, -1);
            $this->sql_campo = substr(trim($this->sql_campo), 0, -1);
        }

        if (isset($this->nuevo)) {//Nuevo Registro
            echo $this->sql = " INSERT INTO " . $this->tabla . " (" . $this->sql_campo . ") VALUES (" . $this->sql_valor . "); ";
        } else {//Actualización de Registro
            echo $this->sql = "UPDATE  " . $this->tabla . "  SET  " . substr($this->sql_valor, 0, -2);
            $this->sql.= " WHERE " . $tipo_id . " in (" . $this->id . ")";
        }
        $this->ejecutarSentenciaMysql();
        if (isset($this->nuevo))//Nuevo Registro
            $this->id = $this->verIdMysql();
        unset($this->nuevo, $this->sql_campo, $this->sql_valor);
    }

    public function ejecutar_queryMysql($query) {
        $this->abrirConexionMysql();
        $this->sql = $query;
        $data = $this->ejecutarSentenciaMysql();
        return $data;
    }

    public function setConfigMysql($tabla, $id = null) {
        $this->tabla = $tabla;
        $this->id = $id;

        if (!$id) {
            $this->nuevo = true;
//            $operacion = 'nuevo';
        } else {
//            $operacion = 'editar';
        }
//        $this->historico($this->id, $operacion);
    }

    function abrirConexionMysql($tipo = "admin") {

        $mysqli = new mysqli($this->mysql_host, $this->mysql_user, $this->mysql_pass, $this->mysql_bd);
        mysqli_set_charset($mysqli, "utf8");

        /* verificar la conexión */
        if (mysqli_connect_errno()) {
            printf("Falló la conexión failed: %s\n", $mysqli->connect_error);
            exit();
        }

        $this->conexMysql = $mysqli;

        return $this->conexMysql;
    }

    function ejecutarSentenciaMysql() {
        $this->abrirConexionMysql();


        if ($resultado = mysqli_query($this->conexMysql, $this->sql)) {
            for ($i = 0; $data[$i] = mysqli_fetch_assoc($resultado); $i++)
                ;
        }
        array_pop($data);

        return $data;
    }

    function verIdMysql() {
        return $this->conexMysql->insert_id;
    }

} ?>
