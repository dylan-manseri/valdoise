<?php

require_once "User.class.php";

class DatabaseManager
{
    private string $hostname;
    private string $usernameDB;
    private string $password;
    private string $dbname;
    private int $port;

    /**
     * @param string $hostname
     * @param string $socket
     * @param int $port
     * @param string $database
     * @param string $password
     * @param string $usernameDB
     */
    public function __construct()
    {
        $config = require_once "secret/config.conf.php";
        $this->hostname = $config['hostname'];
        $this->port = $config['port'];
        $this->dbname = $config['dbname'];
        $this->password = $config['password'];
        $this->usernameDB = $config['username'];
    }

    public function connection(): void
    {
        try{
            $dsn = 'mysql:host='.$this->hostname.';port='.$this->port.';dbname='.$this->dbname;
            $dbh = new PDO($dsn, $this->usernameDB, $this->password);
            $pdoStat = $dbh->query('SELECT * FROM accounts;');
            $tab = $pdoStat->fetchAll();
            echo $tab[0][0].' '.$tab[0][1].' '.$tab[0][2].' '.$tab[0][3];
        }catch(PDOException $e){
            echo 'Nous rencontrons actuellement un problème avec notre base de donnée';
        }
    }
}