<?php
namespace AppBundle\Event;


final class HplEvents
{
    //订单退回event
    const ORDER_BACK = 'hpl.order_back';

    //订单审核结果event
    const ORDER_FINISH = 'hpl.order_finish';

    //订单提交event
    const ORDER_SUBMIT = 'hpl.order_submit';

    //又一车审核师初审完成event
    const ORDER_PRIMARY_EXAM = 'hpl.order_primary_exam';
}