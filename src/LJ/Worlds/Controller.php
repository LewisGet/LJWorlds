<?php

namespace LJ\Worlds;

use Windwalker\Database\DatabaseFactory;

class Controller
{
    public $db;

    public function doExcute()
    {
        $this->initDB();

        $redirect = false;

        try
        {
            if (isset($_GET['a']))
            {
                $this->newVN();
            }
            else
            {
                $input = $this->idCheck();
            }
        }
        catch (\Exception $e)
        {
            $redirect = true;
        }

        if ($redirect)
        {
            $this->getRedirectToNewVN();
            return "";
        }

        return (new \LJ\Worlds\View())->display($input->vo, $input->no);
    }

    public function initDB()
    {
        $options = array(
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'password' => '',
            'port'     => 3306,
            'database' => 'ljWorlds',
        );

        /**
         * https://docs.google.com/spreadsheets/d/1lnkXZ7Orc-K5w3TB4ncCaGwgxA4h4GwF_JJtch92Wzs/edit#gid=179802569
         */

        $this->db = DatabaseFactory::getDbo('mysql', $options);
    }

    public function getRedirectToNewVN()
    {
        $vs = $this->db->setQuery("Select * FROM `words` where `type` = 1")->loadAll();
        $ns = $this->db->setQuery("Select * FROM `words` where `type` = 0")->loadAll();

        $ids = $this->getNoMappingVN($vs, $ns);

        $vid = $ids['v']->id;
        $nid = $ids['n']->id;

        header("Location: app.php?v={$vid}&n={$nid}");

        return '';
    }

    public function getNoMappingVN($vs, $ns)
    {
        foreach ($vs as $v)
        {
            foreach ($ns as $n)
            {
                $mapping = $this->db->setQuery("select * from `vn_mapping` where `vid` = {$v->id} and `nid` = {$n->id}")->loadOne();

                if (empty($mapping))
                {
                    return array('v' => $v, 'n' => $n);
                }
            }
        }

        return array(null);
    }

    public function idCheck($checkA = false)
    {
        $vid = intval($_GET['v']);
        $v = $this->db->setQuery("Select * FROM `words` where `id` = $vid")->loadOne();

        if (empty($v))
        {
            throw new \Exception("not found v");
        }

        if ($v->type != 1)
        {
            throw new \Exception("error v");
        }

        $nid = intval($_GET['n']);
        $n = $this->db->setQuery("Select * FROM `words` where `id` = $nid")->loadOne();

        if (empty($n))
        {
            throw new \Exception("not found n");
        }

        if ($n->type != 0)
        {
            throw new \Exception("error n");
        }

        $a = $_GET['a'];

        if ($checkA and !in_array($_GET['a'], array("1", "0")))
        {
            throw new \Exception("a have to be 1 or 0");
        }

        return (object) array('vo' => $v, 'no' => $n, 'v' => $vid, 'n' => $nid, 'a' => intval($a));
    }

    public function newVN()
    {
        $input = $this->idCheck(true);

        $this->db->setQuery("INSERT INTO `vn_mapping` (vid, nid, allow) value ({$input->v}, {$input->n}, {$input->a})");
        $this->db->execute();

        $this->getRedirectToNewVN();

        return true;
    }

    public function editNV()
    {

    }

    public function editVN()
    {

    }
}