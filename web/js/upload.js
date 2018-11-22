var Upload = function(){}

Upload.prototype = {
    init:function(o){ 
        this.start(o);
        this.handle();
        this.createUploader();
    },
    start:function(o){
        var $add = o.$add;
        o.id = $add.attr("id");
        o.containerId = $add.parents(".module-content").attr("id");
        o.title = $add.find(".title").text();
        o.most = $add.data("most");
        o.least = $add.data("least");
        o.name = $add.data("key")+"[]";
        this.o = o;
    },
    handle:function(){
        var self = this;
        var dataKey = this.o.$add.data("key");
        var expr = "li.finish[data-key='"+dataKey+"'] .delete";
        this.o.$form.on("click",expr,function(){
            self.deleteItem($(this).parents("li"));
        })
    },
    createUploader:function(){
        var self = this;
        var o = this.o;
        var dataKey = o.$add.data("key"); 
        this.uploader = o.qiniu.uploader({
            runtimes: 'html5,flash,html4',
            browse_button: o.id,
            uptoken : o.uptoken,
            unique_names: true,
            domain: o.domain,
            container: o.containerId,
            max_file_size: '10mb',
            flash_swf_url: o.flash_swf_url,
            max_retries: 3,
            dragdrop: true,
            drop_element: o.id,
            chunk_size: '10mb',
            auto_start: false,
            save_key: true,
            filters: {
                mime_types: [
                    {title : "Image files", extensions : "jpg,gif,png"}, // 限定jpg,gif,png后缀上传
                ],
                max_file_size: '5mb'
            },
            init: {
                'FilesAdded': function(up, files){
                    var oldLength = o.$add.prevAll("li[data-key='"+dataKey+"']").length;
                    var itemHTML="";

                    if(o.most !== -1 && o.most-oldLength <= files.length){
                        files.splice(o.most-oldLength,files.length-(o.most-oldLength));
                        o.$add.addClass('hide');
                    }

                    for(var i=0;i<files.length;i++){
                        itemHTML = self.createItem(dataKey);
                        o.$add.before(itemHTML);
                    }

                    up.start();
                    up.refresh();
                },
                'BeforeUpload': function(up, file) {
                    
                },
                'UploadProgress': function(up, file) {

                },
                'FileUploaded': function(up, file, info) {
                    var info = JSON.parse(info);
                    
                    var dataDefault = o.qiniu.imageView2({
                        mode: 1, 
                        w: o.$add.data("imgWidth"),       
                        h: o.$add.data("imgHeight") 
                    }, info.key);

                    var dataOriginal = o.qiniu.getUrl(info.key);

                    var $item = o.$add.prevAll("li.uploading[data-key='"+dataKey+"']").eq(0);

                    $item.removeClass('uploading').addClass('finish');
                    $item.append('<input class="pic-input" hidden="" name="'+o.name+'" value="'+info.key+'">');

                    $item.find("img").attr({
                        "data-cut" : dataDefault,
                        "data-original" : dataOriginal+"?imageMogr2/auto-orient/quality/80!"
                    });

                    self.updateOrder();

                },
                'Error': function(up, err, errTip) {
                    var $itemAll = o.$add.prevAll("li.uploading[data-key='"+dataKey+"']");
                    $itemAll.remove(); 
                    commonMethod.showTopTips(errTip);
                },
                'UploadComplete': function(up, files) {

                }
            }
        });
    },
    createItem:function(key){
        var itemHTML =  '<li class="uploading" data-key="'+key+'">'
                     +      '<div class="pic-box"><img class="pic" alt="'+this.o.title+'" src="'+this.o.defaultImg+'"></div>'
                     +      '<div class="title" title="'+this.o.title+'">'+this.o.title+'</div>'
                     +      '<span class="delete"><i class="icon-font-shanchu"></i></span>'
                     +  '</li>';
        return itemHTML;
    },
    deleteItem:function($item){
        var self =  this;
        commonMethod.prompt({
            title:"删除图片",
            msgHTML:"确定删除吗",
            confirm:function(){ 
                $item.remove();
                self.o.$add.removeClass('hide');
                self.uploader.refresh();
                self.updateOrder();
            }
        })
    },
    updateOrder:function(){
        workMethod.loadImageList($('#gallery'));//加载图片列表
        workMethod.setInitViewer($("#gallery"),"data-original",true);//重置放大图片插件
        this.o.updateOrder && this.o.updateOrder();
    }
}