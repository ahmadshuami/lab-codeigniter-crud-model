<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Abstract class MY_Model
 * 
 * Handle basic database CRUD operation. This is not going to be an ORM library.
 * 
 * @package     CodeIgniter
 * @author     	Ahmad Shuami  <shuami79@yahoo.com>
 * @copyright	Copyright (c) 2019, Ahmad Shuami
 * 
 * @license     MIT License     <https://opensource.org/licenses/MIT>
 * @link        https://www.bicarailmu.com
 * @link        https://github.com/ahmadshuami/lab-codeigniter-crud-model
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
     * Set default property value.
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
     * Get uuid field.
     * 
     * This method will return a value for a selected id field. This method will be used if id @ uuid field
     * is not set as an auto increment in database. You can call this method from a controller as below:
     * 
     * $this->model_name->get_uuid('user_uuid', 'userTable', ['id' => 1]);
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
     * Insert record into database.
     * 
     * Use this method to add new record into database. You can call this method from a contorller as below:
     * 
     * $data = [
     *      'fullname'  => $this->input->post('fullname'),
     *      'email'     => $this->input->post('email')
     * ];
     * $result = $this->model_name->add_record($data, 'tblUser', $uuid = 'userId');
     * 
     * if $uuid variable is set as NULL, it means the default value for the uuid field is set as an auto increment
     * in database. If the variable is set with other value (other than NULL), it means the default value for the uuid
     * field is not set as an auto increment and the variable $uuid will be set as a UUID value. 
     *
     * @param  mixed $arrayQuery
     * @param  string $tableName
     * @param  string $uuid
     *
     * @return boolean
     */
    public function add_record($arrayQuery, $tableName, $uuid = NULL) 
    {
        $this->uuid = $uuid;

        // if user define uuid value
        if ($this->uuid) {
            // MYSQL uuid without dash '-'
            // $this->db->set($uuid,"replace(uuid(),'-','')", FALSE); 

            // MYSQL uuid with dash '-'
            $this->db->set($this->uuid,"uuid()", FALSE);

            // POSTGRESQL uuid without dash '-'
            // $this->db->set($this->uuid,"replace(md5(((uuid_generate_v4())::character varying)::text), '-'::text, ''::text)", FALSE);
        }

        $result = $this->db->insert($tableName, $arrayQuery);

        return (bool) $result;
    }

    /**
     * Update record in database.
     * 
     * Use this method to update existing record in database. You can call this method from a controller as below:
     * 
     * $data = [
     *      'fullname'  => $this->input->post('fullname'),
     *      'email'     => $this->input->post('email')
     * ];
     * $where = ['id' => $id];
     * $result = $this->model_name->update_record($data, 'tblUser', $where);
     *
     * @param  mixed $arrayQuery
     * @param  string $tableName
     * @param  mixed $whereEntry
     *
     * @return boolean
     */
    public function update_record($arrayQuery, $tableName, $whereEntry) 
    {
        $result = $this->db
            ->where($whereEntry)
            ->update($tableName, $arrayQuery);
        
        return (bool) $result;
    }

    /**
     * Get record from database.
     * 
     * Use this method to get a record from database. You can use $flag = 'all' if you want to get multiple record.
     * You can call this method from a controller as below (to be updated):
     * 
     * $arr_query = [
     *      'selColumn'         => $selColumn,
     *      'selJoin'           => $selJoin,
     *      'whereEntry'        => $whereEntry,
     *      'orWhereEntry'      => $orWhereEntry,
     *      'whereInEntry'      => $whereInEntry,
     *      'whereNotInEntry'   => $whereNotInEntry,
     *      'like'              => $like,
     *      'orLike'            => $orLike,
     *      'groupBy'           => $groupBy,
     *      'orHaving'          => $orHaving,
     *      'orderBy            => $orderBy,
     *      'limit              => $limit
     * ];
     * $this->model_name->get_record($arr_query, 'userTable', $flag = 'all');
     * 
     * $arr_query array values is depend on your query. (e.g. If your query does not have orderBy, so you can exclude the value)
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

        // if whereNotInEntry not empty
        if (!empty($arrayQuery['whereNotInEntry'])) {
            foreach($arrayQuery['whereNotInEntry'] as $val) {
                $this->db->where_not_in($val['colName'],$val['colValue']);
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
                // return all rows
                return $result->result_array();
            } else {
                // return single row
                return $result->row_array();
            } 
        }
    }

    /**
     * Remove record from database permanently.
     * 
     * Use this method if you want to delete record permanently from database. You can call this method
     * from a controller as below:
     * 
     * $this->model_name->hard_delete(['id' => $id], 'tblUser');
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
     * Get total record.
     * 
     * Use this method if you want to get total record from a selected table. You can call this method
     * from a controller as below (to be updated):
     * 
     * $arr_query = [
     *      'whereEntry'        => $whereEntry,
     *      'whereInEntry'      => $whereInEntry
     * ];
     * $this->model_name->get_record($arr_query, 'userTable');
     *
     * @param  mixed $whereEntry
     * @param  string $tableName
     *
     * @return integer
     */
    public function total_all($arrayQuery, $tableName)
    {
        $this->db->from($tableName);
        
        // if whereEntry not empty
        if (!empty($arrayQuery['whereEntry'])) {
            $this->db->where($arrayQuery['whereEntry']);
        }

        // if whereInEntry in not empty
        if (!empty($arrayQuery['whereInEntry'])) {
            foreach($arrayQuery['whereInEntry'] as $val) {
                $this->db->where_in($val['colName'], $val['colValue']);
            }
        }
        
        return $this->db->count_all_results();
    }
}
