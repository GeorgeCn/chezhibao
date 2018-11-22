// Copyright (c) 2012 Bugrose+Haozi. All rights reserved.
// Developed by bugrose(http://www.bugrose.com)
// Desgined by haozi(http://www.daqianduan.com)

(function(){
	var blobBuilder, blob, BlobBuilder = this.WebKitBlobBuilder || this.MozBlobBuilder || this.BlobBuilder;
	var URL = this.webkitURL || this.mozURL || this.URL;
	var g_downResData = [];//所有勾选的要下载的资源信息
	var g_downResData_Flag = false;//是否下载完毕的标记
	var fileName = [];//下载图片名字
	var j=0;

	function onerror(message) {
		console.error(message);
	}

	function initPopEvents(imgbox, downloadButton, orderId, event){
		Array.prototype.forEach.call(imgbox,function(item,index) {
			var i = item.getAttribute("alt")|| "未知";
			var v = item.getAttribute("data-original");
			var t = "image/"+v.split(".")[0];
			var repeatName = 0;//重复的名字

			for(var j = 0;j<fileName.length;j++){
				if(fileName[j].indexOf(i)==0){
					repeatName++;
				}
			}

			if(fileName.indexOf(i)>-1){
				i = i+repeatName;
			}

			fileName.push(i);

			g_downResData[i] = [v,false,false,i]; 
			g_downResData_Flag = false;
			var request = new XMLHttpRequest();

			request.addEventListener("load", function() {
				if (request.status == 200) {
				    	var blob = new Blob([request.response], {type: t});
				    	g_downResData[i][1] = blob;
				    	g_downResData_Flag = true;
				}
			}, false);
			request.open("GET", v);
			request.responseType = 'blob';
			request.send();
		});

		zip.createWriter(new zip.BlobWriter(), function(zipWriter) {
			global_zipWriter = zipWriter;
			nextFile(downloadButton, event, orderId);
		}, onerror);
	}

	//获取尚未打包的文件
	function getUnPackageInfo(){
		for(var i in g_downResData){
			if(!g_downResData[i][2] && g_downResData[i][1]!=false){
				return g_downResData[i];
			}
			continue;
		}
		return false;
	}

	function resetPackageStatus(){
		fileName = [];
		for(var i in g_downResData){
			g_downResData[i][2] = false;
		}
	}


	function nextFile(downloadButton, event, orderId) {
		if(g_downResData_Flag){
			var info = getUnPackageInfo();
			if(!info){
				triggerDownload(downloadButton, event, orderId);
				return false;
			}
			
			//图片命名
			var filename = info[3]+'.jpeg';
			var blob = info[1];

			event.preventDefault();

			console.log(filename);
			global_zipWriter.add(filename, new zip.BlobReader(blob), function() {
				info[2] = true;
				nextFile(downloadButton, event, orderId);
			});

		}else{
			console.log("idle");
			event.preventDefault();
			setTimeout(function(){
				nextFile(downloadButton, event, orderId);
			} ,500);
		}
	}

	function getDownloadName(orderId){
		return "车辆详情图片"+orderId+".zip";
	}

	//点击下载时执行两遍，此方法禁掉
	function triggerDownload(downloadButton, event, orderId){
		if (!downloadButton.download) {
			global_zipWriter.close(function(blob) {
				var blobURL = URL.createObjectURL(blob);

				var clickEvent = document.createEvent("MouseEvent");
				clickEvent.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
				downloadButton.href = blobURL;
				downloadButton.download = getDownloadName(orderId);
				downloadButton.dispatchEvent(clickEvent);


				setTimeout(function() {
					URL.revokeObjectURL(blobURL);
					downloadButton.setAttribute("href","#");
					downloadButton.download = "";
					downloadButton.removeAttribute('disabled');
					resetPackageStatus();
					global_zipWriter = null;
				}, 1);

				zipWriter = null;
			});
			event.preventDefault();
			downloadButton.removeAttribute('disabled');
		}
	}
	window.initPopEvents = initPopEvents;
})(this)

