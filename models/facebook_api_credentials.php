<?php

defined('C5_EXECUTE') or die('Access Denied.');

class FacebookApiCredentials extends Object
{
    protected static $mTable = 'mFacebookApiCredentials';
    protected $api_key, $secret;

    public function FacebookApiCredentials($data = null)
    {
        if ($data) {
            $this->id = $data['id'];
            $this->api_key = $data['api_key'];
            $this->secret = $data['secret'];
        }
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setApiKey($val)
    {
        return $this->api_key = $val;
    }

    public function setSecret($val)
    {
        return $this->secret = $val;
    }

    public function load()
    {
        $credentials = self::get("SELECT * FROM ".self::$mTable." LIMIT 1");

        if ($credentials == null) {
            $db = Loader::db();
            $db->execute("INSERT INTO ".self::$mTable." (api_key,secret) VALUES ('','')");
            $credentials = self::get("SELECT * FROM ".self::$mTable." LIMIT 1");
        }

        return $credentials;
    }

    public function save()
    {
        $db = Loader::db();

        return $db->execute("UPDATE ".self::$mTable." SET api_key=?, secret=? WHERE id = ?", array($this->api_key, $this->secret, $this->id));
    }

    private static function get($sql, $multiple = false)
    {
        $db = Loader::db();
        $rs = $db->execute($sql);
        $resp = null;

        if ($multiple) {
            $resp = array();
            while ($data = $rs->fetchrow()) {
                $resp[] = new FacebookApiCredentials($data);
            }
        } elseif ($data = $rs->fetchrow()) {
            $resp = new FacebookApiCredentials($data);
        }

        return $resp;
    }
}
