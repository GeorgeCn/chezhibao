
//通用方法
var commonMethod = {
    keep:{
        topTipsTimer:null
    },
    //alert
    alert: function(options) {
        this.createTipPop("alert", options);
    },
    //prompt
    prompt: function(options) {
        this.createTipPop("prompt", options);
    },
    //创建提示弹出框
    createTipPop: function(type,options) {
        var common = {
            title: "提示",
            confirmTxt: "确定",
            cancelTxt: "取消",
            customClass:"",//自定义样式
            customEvent:null,//自定义事件
            confirm:null,//确认
            cancel:null,//取消
            preventClose:false//阻止关闭弹出框
        };

        $.extend(common,options);

        var method = {
            remove:function($el){
                $el.remove();
            }
        }

        var confirmBtnHTML = '<span class="pop-btn confirm" data-pop-handle="confirm">' + common.confirmTxt + '</span>';
        var footHTML = type == "alert" ? confirmBtnHTML : '<span class="pop-btn cancel" data-pop-handle="cancel">' + common.cancelTxt + '</span>' +confirmBtnHTML;
        var popHTML = '<div class="pop-tip-bg '+common.customClass+'">' +
            '<div class="pop-tip pop-' + type + '">' +
            '<i class="pop-tip-close" data-pop-handle="close" title="关闭"></i>' +
            '<div class="pop-tip-head"><h5>' + common.title + '</h5></div>' +
            '<div class="pop-tip-main">' + common.msgHTML + '</div>' +
            '<div class="pop-tip-foot">' + footHTML + '</div>' +
            '</div>'
        '</div>';
        var $popTip;

        $("body").append(popHTML);
        $popTip = $("body").find(".pop-tip-bg").last();

        common.customEvent && common.customEvent($popTip,method);

        $popTip.on("click", function(e) {
            var $target = $(e.target);
            var popHandle = $target.data("popHandle");

            if (popHandle) {
                switch (popHandle) {
                    case "confirm":
                        common.confirm && common.confirm($popTip,method);
                        if(common.preventClose){
                            return;
                        }else{
                            break;
                        }
                    case "cancel":
                        common.cancel && common.cancel();
                        break;
                }
                method.remove($popTip);
            }
        })
    },
    //显示topTips
    showTopTips:function(msg){
        var tipsHTML = '<div class="top-tips" id="topTips"></div>';
        var $tips = $("#topTips");
        var $popTip = $(".pop-tip");
        var width,deviation;

        clearTimeout(this.keep.topTipsTimer);

        if(!$tips.length){
            $tips = $(tipsHTML);
            $("body").append($tips);
        }

        if($popTip.length){
            deviation = 0;
        }else{
            deviation = $("#sidebar").outerWidth()/2;
        }

        $tips.text(msg).css("display","block");
        $tips.css({
            "margin-left":-$tips.outerWidth()/2+deviation,
            "opacity":1
        })

        this.keep.topTipsTimer = setTimeout(function(){
            $tips.css({
                "display":"none",
                "opacity":0
            })
        },5000)

    },
    //创建表单弹出框
    createTablePop: function(options) {
        var common = {
            title: "提示",
            tableHTML:null,//自定义表格
            tableData:"",//表格数据
            extraData:null,//自定义数据
            customClass:"",//自定义样式
            customEvent:null,//自定义事件
            preventClose:false//阻止关闭弹出框
        };

        $.extend(common,options);

        var method = {
            remove:function($el){
                $el.remove();
            }
        }

        if(!common.tableHTML) {
            common.tableHTML = '<thead><tr><th class="pop-table-th"></th><th class="pop-table-th">'+ common.extraData[0] +'</th><th class="pop-table-th">'+ common.extraData[1] +'</th></thead>';
            for(var x in common.tableData) {
                if(x == 'field_2011' || x == 'field_2021' || x == 'field_2031') continue;
                if(x == 'field_2061') {
                    common.tableData[x]['old']['value'] = '有差异';
                    common.tableData[x]['new']['value'] = '有差异';
                } else if(x == 'field_2071') {
                    var str_1 = common.tableData[x]['old']['value'];
                    var str_2 = common.tableData[x]['new']['value'];
                    var strs_1 = strs_2 = [];
                    if(common.tableData[x]['old']['value']) {
                        strs_1 = str_1.split(',');
                    }
                    common.tableHTML += '<tr><td>'+ common.tableData[x]['display'] +'</td>';
                        if(strs_1.length == 0) {
                            common.tableHTML += '<td></td>';
                        } else if(strs_1.length == 1) {
                            var img1Src = common.domain+'/'+strs_1[0]+'?imageView2/1/w/88/h/62';
                            common.tableHTML += '<td><img style="width:88px;height:62px" src="'+ img1Src +'"></td>';
                        } else {
                            var img1Src_1 = common.domain+'/'+strs_1[0]+'?imageView2/1/w/88/h/62';
                            var img1Src_2 = common.domain+'/'+strs_1[1]+'?imageView2/1/w/88/h/62';
                            common.tableHTML += '<td><img style="width:88px;height:62px" src="'+ img1Src_1 +'"><img style="width:88px;height:62px" src="'+ img1Src_2 +'"></td>';
                        }
                    if(common.tableData[x]['new']['value']) {
                        strs_2 = str_2.split(',');
                    }
                        if(strs_2.length == 0) {
                            common.tableHTML += '<td></td>';
                        } else if(strs_2.length == 1) {
                            var img2Src = common.domain+'/'+strs_2[0]+'?imageView2/1/w/88/h/62';
                            common.tableHTML += '<td><img style="width:88px;height:62px" src="'+ img2Src +'"></td>';
                        } else {
                            var img2Src_1 = common.domain+'/'+strs_2[0]+'?imageView2/1/w/88/h/62';
                            var img2Src_2 = common.domain+'/'+strs_2[1]+'?imageView2/1/w/88/h/62';
                            common.tableHTML += '<td><img style="width:88px;height:62px" src="'+ img2Src_1 +'"><img style="width:88px;height:62px" src="'+ img2Src_2 +'"></td>';
                        }
                    common.tableHTML += '</tr>';continue;            
                    }
                    common.tableHTML += '<tr><td>'+ common.tableData[x]['display'] +'</td><td>'+ common.tableData[x]['old']['value'] +'</td><td>'+ common.tableData[x]['new']['value'] +'</td></tr>'; 
            }
        }
        
        var popHTML = '<div class="pop-tip-bg '+common.customClass+'">' +
                          '<div class="pop-tip pop-table-special">' +
                          '<i class="pop-tip-close" data-pop-handle="close" title="关闭"></i>' +
                          '<div class="pop-tip-head"><h5>' + common.title + '</h5></div>' +
                          '<div class="pop-table-title">' + common.msgHTML + '</div>' +
                          '<div class="pop-tip-foot pop-table-overflow"><table class="table-new-box-border table-striped" style="border:0px !important;">'+ common.tableHTML +'</table></div>' +
                      '</div>';
        var $popTip;

        $("body").append(popHTML);
        $popTip = $("body").find(".pop-tip-bg").last();
        
        common.customEvent && common.customEvent($popTip,method);

        $popTip.on("click", function(e) {
            var $target = $(e.target);
            var popHandle = $target.data("popHandle");

            if (popHandle) {
                if(common.preventClose) return;
                method.remove($popTip);
            }
        })
    },
    //判断图片是否加载完成
    loadImage: function(src, callback) {
        var img = new Image();
        img.src = src;
        if (img.complete || img.width) {
            callback.call(img);
            return;
        }
        img.onload = function() {
            callback.call(img);
        };
    },
    //获取img实际宽高
    getImgNaturalStyle:function(src,callback) {
        var img = new Image();
        img.src = src;
        if (img.naturalWidth) { 
            callback(src,img.naturalWidth,img.naturalHeight);
        } else {  
            if(img.complete||img.width) { 
                callback(src,img.width,img.height);
            }else{
                img.onload = function () { 
                    callback(src,img.width,img.height);
                };
            }
        }
    },
    //判断ie浏览器(仅支持9包含以下)
    isIE: function() {
        var undef,
            v = 3,
            div = document.createElement("div"),
            all = div.getElementsByTagName("i");

        while (
            div.innerHTML = "<!--[if gt IE " + (++v) + "]><i></i><![endif]-->",
            all[0]
        );

        return v > 4 ? v : undef;
    },
    //设置cookie
    setCookie: function(name,value,days,path){
        var exp = new Date(),cookieStr;
        exp.setTime(exp.getTime() + days*24*60*60*1000);
        cookieStr = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
        if(path){
            cookieStr +=";path=" + path;
        }
        document.cookie = cookieStr;
    }
}