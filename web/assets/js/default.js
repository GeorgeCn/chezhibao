
/*默认执行*/
$(function() {
	//获取"复审"和"已退回"状态数量的路由
	var pathOrderCount = $("#sidebar").data("path-order-count");

	if(pathOrderCount){//间隔2分钟获取"复审"和"已退回"value
		setInterval(function(){
	    	workMethod.getCount(pathOrderCount);
	    },120000)
	}

    //搜索输入框
    $("body").on("blur.had-search","input.had-search",function(){
        $(this).toggleClass('had-value', !!$.trim($(this).val()));
    })

    workMethod.changeVersionWithTip();//改版后首次登录提示

    workMethod.pageTo($("#pageTo"));//分页跳转

    workMethod.downloadReport();//下载报表

    tableNewFix.init();//表格固定标题行和首尾列(必须在设置左侧导航之前执行)

    workMethod.setSideBarNav();//设置左侧导航

    workMethod.logout();//退出账户

    workMethod.loadImageList($('#gallery'));//加载图片列表

    workMethod.showReason();//退回理由展示

    tableFormFilter.init();//表格筛选

})