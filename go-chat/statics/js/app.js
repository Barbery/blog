
if (typeof Chat === 'undefined')
    var Chat = {
        currentChanel : 3,
    };

if (typeof env === 'undefined' || env !== 'develop') {
    Chat.address = "162.243.136.125:12345"
} else {
    Chat.address = "192.168.33.10:12345"
}

Chat.template = '\
    <style type="text/css">\
        .body{\
            margin: 0;\
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;\
            font-size: 14px;\
            line-height: 20px;\
            color: #333;\
            background-color: #fff;\
        }\
        input[disabled], select[disabled], textarea[disabled], input[readonly], select[readonly], textarea[readonly] {\
            cursor: not-allowed;\
            background-color: #eee;\
        }\
    </style>\
    <div ng-controller id="chat_startBtn" class="span3 btn btn-primary btn-large body" style="z-index:9999;position: fixed;bottom: 0;right: 0;padding: 4px;display: block;width: 220px;*display: inline;margin-bottom: 0;*margin-left: 0;font-size: 17.5px;line-height: 20px;color: #fff;text-align: center;text-shadow: 0 -1px 0 rgba(0,0,0,0.25);vertical-align: middle;cursor: pointer;background-color: #006dcc;*background-color: #04c;background-image: linear-gradient(to bottom,#08c,#04c);background-repeat: repeat-x;border: 1px solid #ccc;*border: 0;border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);border-bottom-color: #b3b3b3;-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);*zoom: 1;-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);">\
        聊聊吧\
    </div>\
\
    <div class="span5 body" id="chat_container" style="position: fixed;bottom: 0;right: 0;height: 100%;display: none;border: 1px solid #ccc;background-color: white;z-index: 9999;text-align: left;width: 380px;">\
        <div style="background-color: #f5f5f5;border: 1px solid #ccc;">\
            <span class="add-on" style="padding-left: 10px;">频道：</span>\
            <select id="chat_currentChanel" style="margin-bottom: 0px;margin: 0;font-size: 14px;vertical-align: middle;cursor: pointer;font-weight: normal;line-height: 30px;font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif;display: inline-block;height: 30px;padding: 4px 6px;color: #555;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;*margin-top: 4px;width: 220px;border: 1px solid #ccc;">\
            </select>\
            <span class="btn" style="position: fixed;font-size: 20px;margin-left: 12px;display: inline-block;*display: inline;padding: 4px 12px;margin-bottom: 0;*margin-left: .3em;line-height: 20px;color: #333;text-align: center;text-shadow: 0 1px 1px rgba(255,255,255,0.75);vertical-align: middle;cursor: pointer;background-color: #f5f5f5;*background-color: #e6e6e6;background-image: linear-gradient(to bottom,#fff,#e6e6e6);background-repeat: repeat-x;border: 1px solid #ccc;*border: 0;border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);border-bottom-color: #b3b3b3;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);*zoom: 1;-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);"><a href="https://github.com/Barbery/blog/tree/master/go-chat" target="_blank" style="text-decoration: none;color: #08c;">&#9733;</a></span>\
            <span class="btn" id="chatClose" style="position: fixed;font-size: 20px;right: 0;display: inline-block;*display: inline;padding: 4px 12px;margin-bottom: 0;*margin-left: .3em;line-height: 20px;color: #333;text-align: center;text-shadow: 0 1px 1px rgba(255,255,255,0.75);vertical-align: middle;cursor: pointer;background-color: #f5f5f5;*background-color: #e6e6e6;background-image: linear-gradient(to bottom,#fff,#e6e6e6);background-repeat: repeat-x;border: 1px solid #ccc;*border: 0;border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);border-bottom-color: #b3b3b3;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);*zoom: 1;-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);"><a href="javascript:" style="text-decoration: none;color: #08c;">&#10006;</a></span>\
        </div>\
\
        <div id="chat_messageBox" style="padding: 10px;overflow: auto;"></div>\
\
        <div style="position: fixed;bottom: 20px;width: 100%;border-top: 1px solid rgb(204, 204, 204);padding-top: 4px;">\
            <div class="input-prepend" style="display: inline-block;margin-bottom: 10px;font-size: 0;white-space: nowrap;vertical-align: middle;">\
              <span class="add-on" style="display: inline-block;width: auto;height: 20px;min-width: 16px;padding: 4px 5px;font-size: 14px;font-weight: normal;line-height: 20px;text-align: center;text-shadow: 0 1px 0 #fff;background-color: #eee;border: 1px solid #ccc;vertical-align: top;-webkit-border-radius: 4px 0 0 4px;-moz-border-radius: 4px 0 0 4px;border-radius: 4px 0 0 4px;margin-right: -1px;">@</span>\
              <input id="chat_username" class="span2" type="text" placeholder="昵称" style="margin: 0;font-size: 14px;vertical-align: top;*overflow: visible;line-height: 20px;font-weight: normal;font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif;width: 126px;margin-left: 0;display: inline-block;height: 20px;padding: 4px 6px;margin-bottom: 0;color: #555;-webkit-border-radius: 0 4px 4px 0;-moz-border-radius: 0 4px 4px 0;border-radius: 0 4px 4px 0;border: 1px solid #ccc;-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);-moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);-webkit-transition: border linear .2s,box-shadow linear .2s;-moz-transition: border linear .2s,box-shadow linear .2s;-o-transition: border linear .2s,box-shadow linear .2s;transition: border linear .2s,box-shadow linear .2s;position: relative;*margin-left: 0;">\
            </div>\
            <div style="width: 380px;height: 70px;">\
                <textarea id="chat_content" style="width: 260px;min-height: 70px;margin: 0;font-size: 14px;vertical-align: middle;overflow: auto;height: 20px;font-weight: normal;line-height: 20px;font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif;display: inline-block;padding: 4px 6px;margin-bottom: 10px;color: #555;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;margin-left: 0;border: 1px solid #ccc;-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);-moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);-webkit-transition: border linear .2s,box-shadow linear .2s;-moz-transition: border linear .2s,box-shadow linear .2s;-o-transition: border linear .2s,box-shadow linear .2s;transition: border linear .2s,box-shadow linear .2s;" rows="3" class="span4" placeholder="发送内容"></textarea>\
                <button class="btn btn-large btn-primary" id="chat_send" style="margin-top: 14px;margin: 0;font-size: 17.5px;vertical-align: middle;*overflow: visible;line-height: 20px;cursor: pointer;-webkit-appearance: button;font-weight: normal;font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif;display: inline-block;*display: inline;padding: 11px 19px;margin-bottom: 0;*margin-left: .3em;color: #fff;text-align: center;text-shadow: 0 -1px 0 rgba(0,0,0,0.25);background-color: #006dcc;*background-color: #04c;background-image: linear-gradient(to bottom,#08c,#04c);background-repeat: repeat-x;border: 1px solid #ccc;*border: 0;border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);border-bottom-color: #b3b3b3;-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);*zoom: 1;-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);*padding-top: 7px;*padding-bottom: 7px;" type="button">发送</button>\
            </div>\
\
        </div>\
\
    </div>';

(function(){

    Chat.Socket = {
        ws : '',
        reconnectMaxNum : 3,
        connect : function() {
            Chat.cleanMessageBox();
            // before connet socket, check whether has the history message
            Chat.initMessages();
            Chat.Socket.ws = new WebSocket("ws://" + Chat.address + "/?from=" + Chat.urlInfo.page + "&chanel=" + Chat.currentChanel);

            Chat.Socket.ws.onopen = function(){
                console.log("Socket has been opened!");
            };

            Chat.Socket.ws.onmessage = function(message) {
                // console.log("received: ", message.data);
                var data = JSON.parse(message.data);
                switch (data.Type) {
                    case "message":
                        var time = new Date(data.CreatedAt);
                        data.CreatedAt = time.getHours() + ":" + time.getMinutes();
                        Chat.addMessage(data);
                        break;
                    case "num":
                        Chat.updateNum(data);
                        break;
                }
            };


            Chat.Socket.ws.onclose = function(evt) {
                // console.log(evt)
                if (Chat.Socket.reconnectMaxNum >= 0) {
                    console.log("socket closed, trying to reconnect", Chat.Socket.reconnectMaxNum);
                    Chat.Socket.reconnectMaxNum--;
                    Chat.Socket.connect();
                } else {
                    console.log("socket closed");
                    Chat.lostConnect();
                }
            };
        },

        send : function (message, callback) {
            Chat.Socket.waitForConnection(function () {
                Chat.Socket.ws.send(message);
                if (typeof callback !== 'undefined') {
                  callback();
                }
            }, 200);
        },

        waitForConnection : function (callback, interval) {
            if (Chat.Socket.ws.readyState === 1) {
                callback();
            } else {
                var that = this;
                setTimeout(function () {
                    Chat.Socket.waitForConnection(callback);
                }, interval);
            }
        },
    }

    Chat.send = function (username, content) {
        var data = {
            "username" : username,
            "content"  : content,
            "createdAt": Date.now()
        }
        // console.log("send:", data)
        return Chat.Socket.send(JSON.stringify(data));
    }


    Chat.addMessage = function (data) {
        var color = Chat.objects.username.value == data.Username ? "#0a8cd2" : "#333";
        var msg = '<span class="user_name" style="font-weight:bold;color:' + color +'">@' + data.Username + '</span>: <span class="user_message">' + data.Content + '</span><span style="color: #BBBBBB;font-size: 12px;">(' + data.CreatedAt + ')</span><br>';
        Chat.objects.messageBox.insertAdjacentHTML("beforeend", msg);

        var prevMessages = window.sessionStorage.getItem("messages");
        var messages = {
            1: [],
            2: [],
            3: [],
            4: []
        };
        if (prevMessages != null) {
            messages = JSON.parse(prevMessages)
        }

        messages[Chat.currentChanel].push(msg)
        window.sessionStorage.setItem("messages", JSON.stringify(messages));
    }


    Chat.initMessages = function () {
        var messages = window.sessionStorage.getItem("messages");
        if (messages == null) {
            return;
        }

        Chat.objects.messageBox.insertAdjacentHTML("beforeend", JSON.parse(messages)[Chat.currentChanel].join(""));
    }


    Chat.cleanMessageBox = function(){
        Chat.objects.messageBox.innerHTML = "";
    }


    Chat.updateNum = function(data) {
        var tmpl = '\
            <option value="1" ' + (Chat.currentChanel == 1 ? "selected" : "") + '>本页面(在线: ' + (data.Page[Chat.urlInfo.page] ? data.Page[Chat.urlInfo.page].OnlineNum : 0) + ')</option>\
            <option value="2" ' + (Chat.currentChanel == 2 ? "selected" : "") + '>本域名(在线: ' + (data.Domain[Chat.urlInfo.domain] ? data.Domain[Chat.urlInfo.domain].OnlineNum : 0) + ')</option>\
            <option value="3" ' + (Chat.currentChanel == 3 ? "selected" : "") + '>根域名(在线: ' + (data.RootDomain[Chat.urlInfo.rootDomain] ? data.RootDomain[Chat.urlInfo.rootDomain].OnlineNum : 0) + ')</option>\
            <option value="4" ' + (Chat.currentChanel == 4 ? "selected" : "") + '>世界(在线: ' + (data.World["world"] ? data.World["world"].OnlineNum : 0) + ')</option>';
        Chat.objects.currentChanel.innerHTML = tmpl;
    }


    Chat.initUrl = function() {
        var domain = window.location.href.match(/http[s]?:\/\/([\w\.\-]+)/)[1];
        var domainInfos = domain.split(".");
        if (/\.(com\.cn|com\.hk|gov\.cn|net\.cn|org\.cn)$/.test(domain)) {
            var rootDomain = domainInfos.slice(domainInfos.length - 3, domainInfos.length).join(".")
        } else {
            var rootDomain = domainInfos.slice(domainInfos.length - 2, domainInfos.length).join(".")
        }

        Chat.urlInfo = {
            page : window.location.href.split("#")[0].split("?")[0],
            "domain" : domain,
            "rootDomain" : rootDomain,
            "world" : "world"
        }
    }


    Chat.lostConnect = function() {
        Chat.objects.username.disabled = "disabled";
        Chat.objects.content.disabled = "disabled";
        Chat.objects.sendBtn.disabled = "disabled";
        Chat.objects.sendBtn.innerHTML = "已断开";
    }


    // setTimeout(function(){
        // reference to <head>
        // var head = document.getElementsByTagName('head')[0];

        // var css = document.createElement('link');
        // css.type = "text/css";
        // css.rel = "stylesheet";
        // css.href = 'http://cdn.staticfile.org/twitter-bootstrap/2.3.2/css/bootstrap.min.css';
        // head.appendChild(css);

        document.getElementsByTagName("body")[0].insertAdjacentHTML("beforeend", Chat.template);
        Chat.objects = {
            startBtn : document.getElementById("chat_startBtn"),
            chatContainer : document.getElementById("chat_container"),
            closeBtn : document.getElementById("chatClose"),
            sendBtn : document.getElementById("chat_send"),
            currentChanel : document.getElementById("chat_currentChanel"),
            username : document.getElementById("chat_username"),
            content : document.getElementById("chat_content"),
            messageBox : document.getElementById("chat_messageBox"),
        }


        var username = window.localStorage.getItem("Chat.username");
        if (username != null)
            Chat.objects.username.value = username;

        // defualt join to rootDomain chanel
        if (typeof Chat.currentChanel === 'undefined')
            Chat.currentChanel = 3;

        Chat.initUrl();
        Chat.Socket.connect();

        Chat.swap = function(one, two) {
            one.style.display = 'block';
            two.style.display = 'none';
        }

        Chat.objects.startBtn.onclick = function () {
            Chat.swap(Chat.objects.chatContainer, Chat.objects.startBtn);
        }

        Chat.objects.closeBtn.onclick = function () {
            Chat.swap(Chat.objects.startBtn, Chat.objects.chatContainer);
        }

        Chat.objects.sendBtn.onclick = function(){
            switch (Chat.Socket.ws.readyState) {
                case 0:
                    setTimeout(function(){}, 1000)
                    // no break;
                case 1:
                    if (Chat.objects.username.value != "" && Chat.objects.content.value != "") {
                        Chat.objects.username.disabled = "disabled";
                        window.localStorage.setItem("Chat.username", Chat.objects.username.value);
                        Chat.send(Chat.objects.username.value, Chat.objects.content.value);
                        // clean content
                        Chat.objects.content.value = "";
                    } else {
                        alert("昵称和发送内容不能为空哦")
                    }
                    break;

                default:
                    alert("socket连接已关闭，请刷新重试")
            }
        };

        Chat.objects.currentChanel.onchange = function(){
            var newChanel = this.value
            if (newChanel != Chat.currentChanel) {
                Chat.Socket.reconnectMaxNum++;
                Chat.currentChanel = newChanel;
                Chat.Socket.ws.close(1000);
            }
        };


    // }, 1000);


})()

