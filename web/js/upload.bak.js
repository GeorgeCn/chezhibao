var Upload = {};

Upload.setUptoken = function(uptoken){
    Qiniu.uptoken = uptoken;
};

Upload.init = function(o){
    Qiniu.domain = o.domain;
    this.uploaders = this.uploaders || [];
    this.image_width = o.image_width,
    this.image_height = o.image_height,
    uploader = Qiniu.uploader({
        runtimes: 'html5,flash,html4',
        browse_button: o.browse_button,
        uptoken : o.uptoken,
        unique_names: true,
        domain: o.domain,
        container: o.container,
        max_file_size: '10mb',
        flash_swf_url: o.flash_swf_url,
        max_retries: 3,
        dragdrop: true,
        drop_element: o.drop_element,
        chunk_size: '10mb',
        auto_start: false,
        title: o.title,
        most: o.most,
        least: o.least,
        updateOrder: o.updateOrder,
        showTip: o.showTip,
        save_key: true,
        input_name: o.input_name,
        default_img : o.default_img,
        filters: {
            mime_types: [
                {title : "Image files", extensions : "jpg,gif,png"}, // 限定jpg,gif,png后缀上传
            ],
            max_file_size: '5mb'
        },
        init: {
            'FilesAdded': function(up, files) {
                var o = up.getOption();
                var most = o.most;
                var alt = o.title;

                for (var i = 0; i < up.files.length; i++) {
                    if (most !== -1 && i >= most) {
                        up.files.splice(i);
                        break;
                    }
                    
                    if (typeof(up.files[i].input_dom) === "undefined") {
                        var clone = $('<div class="pick-cont-item"><img class="pic" alt="'+alt+'"><i class="fa fa-trash-o fa-lg"></i><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><div class="pic-des">'+alt+'</div></div>')
                            .insertBefore($(o.browse_button));
                        var remove = $(clone).find(".fa-trash-o")
                            .data("_file", up.files[i])
                            .bind("click", function(){
                                removeFile.apply(this, [up]);
                            });
                        var input = $("<input class='pic_input' name='"+ o.input_name +"' value=''></input>")
                            .appendTo($("#"+o.container));
                        up.files[i].input_dom = input;
                        up.files[i].remove_dom = remove;
                    }
                    if(o.browse_button[0].id == "pick_0" || o.browse_button[0].id == "pick_22"){
                        $(o.browse_button).show(); 
                    }else{
                        $(o.browse_button).hide(); 
                    }
                }
                updateTitle(up);
                up.refresh();
                setTimeout(function(){
                    up.start();
                }, 0);
            },
            'BeforeUpload': function(up, file) {
                // 每个文件上传前,处理相关的事情
            },
            'UploadProgress': function(up, file) {
            },
            'FileUploaded': function(up, file, info) {
                var o = up.getOption();
                var info = JSON.parse(info);
                $(file.input_dom).val(info.key);
                $(file.remove_dom).parent()
                    .removeClass("state-loading")
                    .addClass("state-finish");

                var imgLink = Qiniu.imageView2({
                   mode: 1,                     // 缩略模式，共6种[0-5]
                   w: Upload.image_width,       // 具体含义由缩略模式决定
                   h: Upload.image_height,      // 具体含义由缩略模式决定
                }, info.key);
                $(file.remove_dom).parent()
                    .find(".pic")
                    .attr("src",o.default_img)
                    .attr("data-default", imgLink)
                    .attr("data-original", Qiniu.getUrl(info.key));
                if (o.updateOrder) {
                    o.updateOrder();
                }
                defaultInteractive.loadImageList($(file.remove_dom).parent());
               
            },
            'Error': function(up, err, errTip) {
                var o = up.getOption();
                console.log(err);
                console.log(errTip);
                if (o.showTip) {
                    o.showTip(errTip);
                }
            },
            'UploadComplete': function(up, files) {

            }
        }
    });
    uploader.validate = function(){
        var o = this.getOption();
        var least = o.least;
        if (least != -1 && this.files.length < least) {
            return false;
        }
        for (var i = 0; i < this.files.length; i++) {
            if(this.files[i].status !== plupload.DONE){
                return false;
            }
        }
        return true;
    }

    var updateTitle = function(up){
        var o = up.getOption();
        var least = o.least;
        var most = o.most;
        var title = $("#"+o.container).find(".pick-title");
        var value = o.title;
        if (least == -1 && most == -1) {
            value = o.title;
        }
        else if (least == -1) {
            value = o.title+"（最多上传"+most+"张，已选择"+up.files.length+"张）";
        }
        else if (most == -1) {
            value = o.title+"（至少上传"+least+"张，已选择"+up.files.length+"张）";
        }
        title.text(value);

        if (most !=-1 && up.files.length >= most) {
            $("#"+o.container).find(".add").hide();
        }
        else{
            $("#"+o.container).find(".add").show();
        }
    };

    var removeFile = function(up, file_index){
        var file = $(this).data("_file");
        var index = up.files.indexOf(file);
        if (index === -1) {
            return;
        }
        bootboxConfirm({
            title:"确定删除吗",
            callback:function(result){
                if(result){
                    up.files.splice(index, 1);
                    $(file.input_dom).remove();
                    $(file.remove_dom).parent().remove();
                    updateTitle(up);
                    var o = up.getOption();
                    if (o.updateOrder) {
                        o.updateOrder();
                    }
                    console.log($(o.browse_button));
                    $(o.browse_button).show();
                }
            }
        })
    };

    // 初始化ui
    var inputs = $("#"+o.container+" .pic_input");
    for (var i = 0; i < inputs.length; i++) {
        file = new plupload.File(inputs[i].value);
        file.status = plupload.DONE;
        uploader.files.push(file);
        file.input_dom = inputs[i];
    }
    var images = $("#"+o.container+" .state-finish");
    for (var i = 0; i < images.length; i++) {

        var remove = $(images[i]).find(".fa-trash-o")
            .data("_file", uploader.files[i])
            .bind("click", (function(up){
                return function(){
                    removeFile.apply(this, [up]);
                };
            })(uploader));
            console.log(remove);
        uploader.files[i].remove_dom = remove;
    }
    updateTitle(uploader);
    return uploader;
};