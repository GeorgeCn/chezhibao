
//新表格标题行和首尾列固定
var tableFormFilter = {
    init: function() {
        this.$tableFormFilter = $("#formFilterBar");
        if(this.$tableFormFilter.length){
            this.start();
            this.handle();
        }
    },
    start: function() {
        var self = this;

        this.predefinedName();
        this.predefinedList();

        this.$tableFormFilter.find(".filter-input").each(function(index, el) {
            var name = $(this).data("formName");
            var value = $(this).data("formValue");
            self.createFilterInput($(this),name,value);
            self.changeCurrentInput($(this),name,value);
        });

        this.$tableFormFilter.find(".filter-select").each(function(index, el) {
            var name = $(this).data("formName");
            var value = $(this).data("formValue");
            self.createFilterSelect($(this),name,value);
            self.changeCurrentSelect($(this),name,value);
        });
    },
    handle: function() {
        var self = this;
        var $appendBox = this.$tableFormFilter.find(".append-box");
        this.$tableFormFilter.on("click",".value-current",function(){
            var $current = $(this).next(".append-box");
            $appendBox.each(function(){
                if($(this).is($current)){
                    $(this).toggleClass('show');
                }else{
                    $(this).removeClass('show');
                }
            })
        })
        this.$tableFormFilter.on("click",".form-submit",function(){
            self.submit();
        })
        this.$tableFormFilter.on("keydown","input.had-search-end",function(e){
            var value = $.trim($(this).val());
            var key = e.whick || e.keyCode;
            if (key == 13) {
                self.submit();
            }
        })
        this.$tableFormFilter.on("click",".append-box li",function(){
            var name = $(this).parents(".filter-select").data("formName");
            var value = $(this).data("selectValue");
            $(this).parents('.append-box').removeClass('show');
            if(name==self.name.date){
                self.selectDate(name,value);
            }else{
                self.selectItem(name,value);
            }
        })
        this.$tableFormFilter.on("input propertychange",".append-box input.had-search",function(e){
            var name = $(this).parents(".filter-select").data("formName");
            if (name === 'vars[company]') {
                self.filterList($(this).val(),self.companyList,$(this).parents(".filter-select"));
            } else {
                self.filterList($(this).val(),self.agencyList,$(this).parents(".filter-select"));
            }
        });
        this.$tableFormFilter.on("keydown",".append-box input.had-search",function(e){
            var name = $(this).parents(".filter-select").data("formName");
            var value = $.trim($(this).val());
            var key = e.whick || e.keyCode;
            if (key == 13) {
                self.selectItem(name,value);
            }
        })
        $("body").on("click.form.filter",function(e){
            var $target = $(e.target);
            if(!($target.hasClass('value-current')||$target.hasClass('value-current')||$target.hasClass('append-box')||$target.parents(".append-box").length)){
                $appendBox.removeClass('show');
            }
        })
    },
    //预定义name
    predefinedName:function(){
        //特殊input placeholder以及特殊select(日期、总部)需预定义name用于判断
        this.name = {
            mixed : "vars[mixed]",
            date: "vars[dateType]|vars[startDate]|vars[endDate]",
            company: "vars[company]",
            agency: "vars[agency]"
        }
    },
    //预定义select list
    predefinedList:function(){
        this.list = {
            "vars[status]": [['全部','3'],['待审核','10'],['通过','1'],['拒绝','2']],
            "vars[type]": [['全部',"0"],['试用账号',"1"],['合作账号',"2"],['内部账号',"3"],['临时账号',"4"]],
            "vars[dateType]": [['全部时间','10'],['今天','1'],['昨天','-1'],['最近7天','7'],['本月','030'],['最近90天','90'],['最近1年','365'],['自定义时间段','9999']],
            "vars[stage]": [['全部','0'],['待审核','20'],['审核中','30'],['待确认基本信息','1'],['待确认车型','2'],['待确认配置','3'],['待总结','4'],['待核价','5']],
            "vars[orderStatus]": [['全部','0'],['初次提交','1'],['重新提交','2'],['第三方退回','3']],
            "vars[applyStatus]": [['全部','3'],['授权中','0'],['授权完成','1'],['授权取消','2']],
        }
    },
    //提交表单
    submit:function(ready){
        this.$tableFormFilter.find(".append-box.show").removeClass('show');
        this.$tableFormFilter.find("input[type='text']").each(function(){
            var value = $.trim($(this).val());
            $(this).val(value);
        })
        ready && ready();
        this.$tableFormFilter.submit();
    },
    //默认选择模拟select项
    selectItem:function(name,value){
        // 公司和经销商联动，如果公司选择变了后，经销商之前选择的值为空字符
        if (name == 'vars[company]') {
            var companyOldValue = $("input[name='vars[company]']").val();
            var companyCurrentValue = value;
            if (companyOldValue != companyCurrentValue) {
                $("input[name='vars[agency]']").val('');
            }
        }

        this.$tableFormFilter.find("input[name='"+name+"']").val(value);
        this.submit();
    },
    //选择日期模拟select项
    selectDate:function(name,value){
        var name = name.split("|");
        var $inputDateType = this.$tableFormFilter.find("input[name='"+name[0]+"']");
        var $inputDateStart = this.$tableFormFilter.find("input[name='"+name[1]+"']");
        var $inputDateEnd = this.$tableFormFilter.find("input[name='"+name[2]+"']");
        var now = new Date();
        var day = now.getDate();
        var start = new Date();
        var dateToday,
            dateYesterday,
            dateStart,
            dateEnd;

        dateToday = this.formatDate(now);

        now.setDate(day-1);

        dateYesterday = this.formatDate(now);

        switch (value.toString()){
            case "10":
                $inputDateStart.val("");
                $inputDateEnd.val("");
                break;
            case "1":
                $inputDateStart.val(dateToday);
                $inputDateEnd.val(dateToday);
                break;
            case "-1":
                $inputDateStart.val(dateYesterday);
                $inputDateEnd.val(dateYesterday);
                break;
            case "7":
            case "90":
            case "365":
                start.setDate(day-parseInt(value));
                dateStart = this.formatDate(start);
                $inputDateStart.val(dateStart);
                $inputDateEnd.val(dateYesterday);
                break;
            case "030":
                start.setDate(1);
                dateStart = this.formatDate(start);
                $inputDateStart.val(dateStart);
                $inputDateEnd.val(dateToday);
                break;
            case "9999":
                this.showCustomDate($inputDateType,$inputDateStart,$inputDateEnd,value);
                return;
        }
        $inputDateType.val(value);
        this.submit();
    },
    //展示自定义时间弹框
    showCustomDate:function($inputDateType,$inputDateStart,$inputDateEnd,value){
        var self = this;
        var today = this.formatDate(new Date());
        var msgHTML =   '<div class="custom-date-content">'
                    +       '<dl>'
                    +           '<dt>开始日期</dt>'
                    +           '<dd class="date-box date">'
                    +               '<input type="text" class="underline start" value="'+today+'" />'
                    +               '<span class="input-group-addon"><i class="icon-font-riqi"></i></span>'
                    +           '</dd>'
                    +       '</dl>'
                    +       '<dl>'
                    +           '<dt>结束日期</dt>'
                    +           '<dd class="date-box date">'
                    +               '<input type="text" class="underline end" value="'+today+'" />'
                    +               '<span class="input-group-addon"><i class="icon-font-riqi"></i></span>'
                    +           '</dd>'
                    +       '</dl>'
                    +   '</div>';

        commonMethod.prompt({
            customClass:"custom-date",
            title:"选择日期范围",
            msgHTML:msgHTML,
            preventClose:true,
            customEvent:function(){
                workMethod.setDatepicker($(".custom-date-content .date"));
            },
            confirm:function($popTip,method){
                var $start = $popTip.find("input.start");
                var $end = $popTip.find("input.end");
                var start = $.trim($start.val());
                var end = $.trim($end.val());
                var dateStart,dateEnd;

                if(!start&&!end){
                    commonMethod.showTopTips("请选择日期");
                }else{
                    if(start&&end){
                        dateStart = self.createDate(start);
                        dateEnd = self.createDate(end);
                        if(dateStart.getTime()>dateEnd.getTime()){
                            commonMethod.showTopTips("开始日期不能大于结束日期");
                            return;
                        }
                    }

                    $inputDateType.val(value);
                    $inputDateStart.val(start);
                    $inputDateEnd.val(end);
                    method.remove($popTip);
                    self.submit();
                }
            }
        })
    },
    //修改CurrentSelect
    changeCurrentSelect:function($el,name,value){
        var txt = "";
        switch (name){
            case this.name.date:
                value = value.split("|");
                if(value[0]=="9999"){
                    if(value[1]&&value[2]){
                        txt = value[1]+"至"+value[2];
                    }else if(!value[1]&&value[2]){
                        txt = value[2]+"以前";
                    }else if(value[1]&&!value[2]){
                        txt = value[1]+"至今";
                    }else{
                        return;
                    }
                    $el.find(".value-current").text(txt).attr("title",txt);
                }else{
                    this.assistChangeCurrentSelect($el,this.list['vars[dateType]'],value[0]);
                }
                return;
            case this.name.company:
                if(value!=""){
                    $el.find(".value-current").text(value).attr("title",value);
                } else {
                    
                }
                return;
            case this.name.agency:
                if(value!=""){
                    $el.find(".value-current").text(value).attr("title",value);
                }
                return;
            default :
                this.assistChangeCurrentSelect($el,this.list[name],value);
        }
    },
    //辅助修改CurrentSelect
    assistChangeCurrentSelect:function($el,list,value){
        for(var i=0;i<list.length;i++){
            if(value==list[i][1]){
                $el.find(".value-current").text(list[i][0]).attr("title",list[i][0]);
                break;
            }
        }
    },
    //修改CurrentInput
    changeCurrentInput:function($el,name,value){
        value = $.trim(value);
        if(value){
            $el.find(".value-current").text(value).attr("title",value);
        }
    },
    //创建可输入的筛选input
    createFilterInput:function($el,name,value){
        var placeholder = this.getPlaceholder(name,$el);
        var appendHTML = '<div class="search-box append-box">'
                       +    '<input type="text" autocomplete="off" class="underline had-search-end" placeholder="'+placeholder+'" value="'+value+'" name="'+name+'" />'
                       +    '<i class="form-submit"></i>'
                       + '</div>';
        $el.append(appendHTML);
        $el.find(".value-current").attr("title",value);
        if(name==this.name.mixed&&value==""){
            $el.find(".value-current").addClass('no-underline');
        }
    },
    //创建模拟select
    createFilterSelect:function($el,name,value){
        var hadchildSearch = $el.data("childSearch");
        var list = this.getFilterList(name);
        var removeValue = $el.data("removeValue")!==undefined?$el.data("removeValue").toString():"";
        var self = this;
        var listHTML,appendHTML;

        if(removeValue){
            removeValue = removeValue.split("|");
            for(var r=0;r<removeValue.length;r++){
                for(var l=0;l<list.length;l++){
                    if(removeValue[r]==list[l][1]){
                        list.splice(l,1);
                    }
                }
            }
        }

        this.createFilterSelectWithInput(name,value);
        if(!list||list.length==0){
            if(name==this.name.company || name == this.name.agency){//获取总部列表
                $.ajax({
                    url:$el.data("childUrl"),
                    success:function(data){
                        var List = [["全部",""]];
                        var ListHTML = "";
                        var value = $el.find(".append-box .had-search").val();
                        if(data.success){
                            for(var i=0;i<data.results.length;i++){
                                List.push([data.results[i],data.results[i]]);
                            }

                            if (name == 'vars[company]') {
                                self.companyList = List;
                                self.filterList(value,self.companyList,$el);
                            } else {
                                self.agencyList = List;
                                self.filterList(value,self.agencyList,$el);
                            }
                        }
                    }
                })
            }else{
                return;
            }
        }

        listHTML = this.assistCreateFilterSelect(list);

        if(hadchildSearch=="had"){
            appendHTML  = '<div class="value-list append-box child-search">'
                        +    '<div class="search-box-child"><input class="underline had-search" type="text" autocomplete="off" /></div>'
                        +    '<ul>'+listHTML+'</ul>'
                        + '</div>';
        }else{
            appendHTML  = '<div class="value-list append-box">'
                        +    '<ul>'+listHTML+'</ul>'
                        + '</div>';
        }
        $el.append(appendHTML);
    },
    assistCreateFilterSelect:function(list){
        var listHTML = "";
        for(var i=0;i<list.length;i++){
            listHTML += '<li title="'+list[i][0]+'" data-select-value="'+list[i][1]+'">'+list[i][0]+'</li>';
        }
        return listHTML;
    },
    //创建隐藏的筛选input(通过模拟select相关联)
    createFilterSelectWithInput:function(name,value){
        if(name){
            name = name.split("|");
            value = value.toString().split("|");
            for(i=0;i<name.length;i++){
                this.$tableFormFilter.append('<input hidden type="text" name="'+name[i]+'" value="'+(value[i]?value[i]:'')+'" />');
            }
        }
    },
    //获取placeholder
    getPlaceholder:function(name,$el){
        var text = $el.find(".value-current").text();
        var placeholder = text?("请输入"+text):"";
        switch (name){
            case this.name.mixed:
                return "评估单号/业务流水号/车架号后6位/品牌/车系/车型";
        }
        return placeholder;
    },
    //获取模拟select对应的列表
    getFilterList:function(name){
        switch (name){
            case this.name.date:
                return this.list['vars[dateType]'];
            default :
                return this.list[name]||[];
        }
    },
    //根据检索内容展示列表(例：所在总部)
    filterList:function(value,list,$el){
        var result = [];
        var listHTML = "";

        value = $.trim(value);

        if(!list||list.length==0){
            return;
        }

        $.each(list,function(){
            if(this[0].indexOf(value)==0){
                result.push(this);
            }
        })

        listHTML = this.assistCreateFilterSelect(result);
        $el.find(".append-box ul").html(listHTML);
    },
    //格式化日期字符串
    formatDate:function(dt,split){
        var year = dt.getFullYear();
        var month = dt.getMonth()+1;
        var day = dt.getDate();
        var split = split || "/";
        if(month<10){
            month="0"+month;
        }
        if(day<10){
            day="0"+day;
        }
        return year+split+month+split+day;
    },
    //创建日期(需严格传递类似yyyy-MM-dd格式字符串)
    createDate:function(dateStr){
        var dateArr = dateStr.split(/[\/-]/);
        var year=parseInt(dateArr[0]),
            month=parseInt(dateArr[1]),
            day=parseInt(dateArr[2]);

        return new Date(year,month-1,day);
    }
}



