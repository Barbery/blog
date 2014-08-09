# go-chat
=======================

中文说明: [http://www.stutostu.com/?p=1358](http://www.stutostu.com/?p=1358)

this components was written by golang + websocket(html5). it provides a platform for users to chat when users browse the web.


## how to use it?

very easy, just insert the below code into your page 

```
<script type="text/javascript"> 
    window.onload = function() { 
        var js = document.createElement("script"); 
        js.type = "text/javascript"; 
        js.src = "http://stutostu.qiniudn.com/app.js"; 
        document.getElementsByTagName("body")[0].appendChild(js); 
    }; 
</script> 
```



## channels

- page: this channel is created by the page url, and the page url not include `?` and `#` parameter, such as http://stutostu.com/page1 and http://stutostu.com/page1?param1=value1#aa is same page. use scene：when you just want to chat with users browsing this page
- domain: this channel is created by the domian of page url, http://www.stutostu.com/page1 and http://www.stutostu.com/page2 are different page, but has the same domain: "www.stutostu.com", so they can chat with others in this channel
- rootDomain: this channel is wider then domain channel, blog.stutostu.com and news.stutostu.com is different domain, but has the same root domain: "stutostu.com", so they can chat with others in this channel
- world: the biggest channel, everyone who use this components can chat in this page.

notice: go-chat has four channel. so far users only can join in one channel, that means you only receive/send messages from one channel you joined.

