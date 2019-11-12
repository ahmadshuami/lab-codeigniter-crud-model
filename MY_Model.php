<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Abstract class MY_Model
 * 
 * Handle basic database CRUD operation. This is not going to be an ORM library.
 * 
 * @package     CodeIgniter
 * @author     	Ahmad Shuami  <shuami79@yahoo.com>
 * @copyright	Copyright (c) 2017-2019, Ahmad Shuami
 * 
 * @license     MIT License     https://opensource.org/licenses/MIT
 * @link        https://www.bicarailmu.com    https://www.facebook.com/ahmadshuami
 * 
 * @version     1.0.3
 */
abstract class MY_Model extends CI_Model {

    public $uuid = null;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * set_property
     *
     * @param  string $tableName
     *
     * @return mixed
     */
    public function set_property($tableName) 
    {
        $result = $this->db->list_fields($tableName);

        foreach ($result as $prop) {
            $this->$prop = null;
        }
    }

    /**
     * get_uuid
     *
     * @param  string $rowEntry
     * @param  string $tableName
     * @param  mixed $whereEntry
     *
     * @return mixed
     */
    public function get_uuid($rowEntry, $tableName, $whereEntry) 
    {
        $this->db->where($whereEntry);
        
        $result = $this->db->get($tableName);

        if ($result) {
            return $result->row($rowEntry);
        };

    }
    
    /**
     * add_record
     *
     * @param  mixed $arrayQuery
     * @param  string $tableName
     * @param  string $uuid
     *
     * @return mixed
     */
    public function add_record($arrayQuery, $tableName, $uuid) 
    {
        $this->uuid = $uuid;

        // if user define uuid value
        if ($this->uuid) {
            // MYSQL uuid without dash '-'
            // $this->db->set($uuid,"replace(uuid(),'-','')", FALSE); 

            $this->db->set($this->uuid,"uuid()", FALSE);

            // POSTGRESQL uuid without dash '-'
            // $this->db->set($this->uuid,"replace(md5(((uuid_generate_v4())::character varying)::text), '-'::text, ''::text)", FALSE);
        }

        $result = $this->db->insert($tableName, $arrayQuery);

        return (bool) $result;
    }

    /**
     * update_record
     *
     * @param  mixed $arrayQuery
     * @param  string $tableName
     * @param  mixed $whereEntry
     *
     * @return mixed
     */
    public function update_record($arrayQuery, $tableName, $whereEntry) 
    {
        $result = $this->db
            ->where($whereEntry)
            ->update($tableName, $arrayQuery);
        
        return (bool) $result;
    }

    /**
     * get_record
     *
     * @param  mixed $arrayQuery
     * @param  string $tableName
     * @param  string $flag
     *
     * @return mixed
     */
    public function get_record($arrayQuery, $tableName, $flag)
    {
        // if selColumn not empty
        if (!empty($arrayQuery['selColumn'])) {
            $this->db->select($arrayQuery['selColumn']);
        }

        // select from tableName
        $this->db->from($tableName);

        // if selJoin not empty
        if (!empty($arrayQuery['selJoin'])) {
            foreach($arrayQuery['selJoin'] as $jTable) {
                $this->db->join($jTable['joinTable'], $jTable['joinOn'], $jTable['joinType']);
            }
        }

        // if whereEntry not empty
        if (!empty($arrayQuery['whereEntry'])) {
            $this->db->where($arrayQuery['whereEntry']);
        }

        // if orWhereEntry not empty (where must come first)
        // e.g $this->db->where('name !=', 'Joe);
        //     $this->db->or_where('id >', 50);
        //     Produces: WHERE name != 'Joe' OR id > 50
        if (!empty($arrayQuery['orWhereEntry'])) {
            $this->db->or_where($arrayQuery['orWhereEntry']);
        }

        // if whereInEntry in not empty
        if (!empty($arrayQuery['whereInEntry'])) {
            foreach($arrayQuery['whereInEntry'] as $val) {
                $this->db->where_in($val['colName'], $val['colValue']);
            }
        }

        // if like not empty
        if (!empty($arrayQuery['like'])) {
            $this->db->like($arrayQuery['like'], 'both');
        }

        // if orLike not empty
        if (!empty($arrayQuery['orLike'])) {
            $this->db->or_like($arrayQuery['orLike']);
        }

        // if groupBy not empty
        if (!empty($arrayQuery['groupBy'])) {
            $this->db->group_by($arrayQuery['groupBy']);
        }

        // if orHaving not empty
        if (!empty($arrayQuery['orHaving'])) {
            $this->db->or_having($arrayQuery['orHaving']);
        }

        // if orderBy not empty
        if (!empty($arrayQuery['orderBy'])) {
            $this->db->order_by($arrayQuery['orderBy']);
        }

        // if limit not empty
        if (!empty($arrayQuery['limit'])) {
            $this->db->limit($arrayQuery['limit']);
        }

        $result = $this->db->get();

        // print out select statement
        // echo $this->db->get_compiled_select();

        if ($result) {
            if ($flag == 'all') {
                return $result->result_array();
            } else {
                return $result->row_array();
            } 
        }
    }

    /**
     * hard_delete
     *
     * @param  mixed $whereEntry
     * @param  string $tableName
     *
     * @return boolean
     */
    public function hard_delete($whereEntry, $tableName) 
    {
        // if whereEntry empty
        if (empty($whereEntry)) {
            return false;
        }

        $this->db
             ->where($whereEntry)
             ->delete($tableName);
        
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * total_all
     *
     * @param  mixed $whereEntry
     * @param  string $tableName
     *
     * @return integer
     */
    public function total_all($whereEntry, $tableName)
    {
        $this->db->from($tableName);
        
        // if whereEntry not empty
        if (!empty($whereEntry)) {
            $this->db->where($whereEntry);
        }
        
        return $this->db->count_all_results();
    }

}
