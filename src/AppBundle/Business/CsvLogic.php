<?php
namespace AppBundle\Business;
    
use Symfony\Component\HttpFoundation\StreamedResponse;
use AppBundle\Traits\ContainerAwareTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exporter\Writer\CsvWriter;

class CsvLogic {
    use ContainerAwareTrait;

    public function exportCSV($result_count, $get_result, $field_name, $fieldViewName, $get_result_arr=NULL)
    {
        set_time_limit(500);
        foreach ($fieldViewName as &$f) {
            $f = mb_convert_encoding($f, 'utf-8', 'auto');
        }
        $callback = function() use(&$get_result,&$fieldViewName) {
            $em = $this->container->get('doctrine')->getManager();
            $writer = new CsvWriter('php://output', ',', '"', "", true, true);
            $writer->open();
                foreach ($get_result as $value) {
                    $data = [];
                    foreach ($value as $k=>$v) {
                        $data[$fieldViewName[$k]] = mb_convert_encoding($v, 'utf-8', 'auto');
                    }
                    $writer->write($data);
                    unset($value, $data);
                }
            $writer->close();
        };

//            call_user_func($callback);die;
        $field_name.="导出";
        if(!strpos($_SERVER["HTTP_USER_AGENT"],"Firefox")){
            $field_name = urlencode($field_name);
        }
        return new StreamedResponse($callback, 200, array(
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => sprintf("attachment; filename=%s", $field_name.date('Y-m-d').'.csv')
        ));
    }

    public function queryExportCSV($result_count, $query, $get_result, $field_name, &$fieldViewName, $get_result_arr=NULL)
    {
        set_time_limit(500);
        foreach ($fieldViewName as &$f) {
            $f = mb_convert_encoding($f, 'utf-8', 'auto');
        }
        $limit = 100;
        $page = ceil($result_count / $limit);

        $callback = function() use(&$query, &$get_result, &$get_result_arr, &$fieldViewName, &$page, &$limit) {
            $em = $this->container->get('doctrine')->getManager();
            $writer = new CsvWriter('php://output', ',', '"', "", true, true);
            $writer->open();
            $i = 0;
            while ($i < $page) {
                if (is_string($query)) {
                    $offset = $i * $limit;
                    $query .= " LIMIT $offset, $limit";
                    $connection = $em->getConnection();
                    $statement = $connection->prepare($query);
                    $statement->execute();
                    $tmp = $statement->fetchAll();
                    $query = str_ireplace(" LIMIT $offset, $limit", "", $query);
                } else {
                    $query->setFirstResult($i * $limit)
                            ->setMaxResults($limit);
                    $tmp = new Paginator($query);
                }
                if ($get_result_arr) {
                    $tmp = $get_result_arr($tmp);
                }

                $datas = $get_result($tmp);
                unset($tmp);

                foreach ($datas as $value) {
                    $data = [];
                    foreach ($value as $k=>$v) {
                        $data[$fieldViewName[$k]] = mb_convert_encoding($v, 'utf-8', 'auto');
                    }
                    $writer->write($data);
                    unset($value, $data);
                }
                unset($datas);
                $i++;
            }
            $writer->close();
        };

//            call_user_func($callback);die;
        $field_name.="导出";        
        if(!strpos($_SERVER["HTTP_USER_AGENT"],"Firefox")){
            $field_name = urlencode($field_name);
        }
        return new StreamedResponse($callback, 200, array(
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => sprintf("attachment; filename=%s", $field_name.date('Y-m-d').'.csv')
        ));
    }



}