package main

import (
	"code.google.com/p/go.net/websocket"
	"container/list"
	"encoding/json"
	"fmt"
	"html"
	"log"
	"net/http"
	"net/url"
	"regexp"
	"strconv"
	"strings"
)

// var connections = make(map[*websocket.Conn]bool, 0)
var users = make(map[*websocket.Conn]*userinfo)
var chanels = new(chanel)
var messageList = list.New()
var nums = new(statistics)

type userinfo struct {
	username     string
	curentChanel int8
	// createdAt    int16
	page       string
	domain     string
	rootDomain string
}

type message struct {
	Username  string
	Content   string
	Type      string
	CreatedAt float64
}

type chanel struct {
	world      map[string]map[*websocket.Conn]bool
	rootDomain map[string]map[*websocket.Conn]bool
	domain     map[string]map[*websocket.Conn]bool
	page       map[string]map[*websocket.Conn]bool
}

type statistics struct {
	world      map[string]*num
	domain     map[string]*num
	rootDomain map[string]*num
	page       map[string]*num
}

type num struct {
	OnlineNum  int8
	MessageNum int8
}

func socketHandler(ws *websocket.Conn) {
	var err error
	onOpen(ws)
	fmt.Println("onOpen")
	// when connect, return the nums statistics to client
	broadcastNums(ws)

	// fmt.Println(nums)

	for {
		var msg string
		if err = websocket.Message.Receive(ws, &msg); err != nil {
			fmt.Println("Can't receive")
			onClose(ws)
			break
		}

		// users[ws].username = data.Username
		// fmt.Println(msg, users[ws])
		go broadcast(parseMsg(msg, "message", users[ws]), users[ws])
	}
}

func parseMsg(msg string, msgType string, user *userinfo) string {
	var data message
	json.Unmarshal([]byte(msg), &data)
	if user.username == "" {
		data.Username = substr(html.EscapeString(data.Username), 0, 30)
		user.username = data.Username
	} else {
		data.Username = user.username
	}

	data.Content = substr(html.EscapeString(data.Content), 0, 200)
	data.Type = msgType
	m, _ := json.Marshal(data)
	return string(m)
}

func broadcast(msg string, user *userinfo) {
	var err error
	var connections map[*websocket.Conn]bool
	switch user.curentChanel {
	case 1:
		connections = chanels.page[user.page]
	case 2:
		connections = chanels.domain[user.domain]
	case 3:
		connections = chanels.rootDomain[user.rootDomain]
	case 4:
		connections = chanels.world["world"]
	}

	for conn := range connections {
		if err = websocket.Message.Send(conn, msg); err != nil {
			fmt.Println("Can't send")
			onClose(conn)
		}
	}
}

func broadcastNums(ws *websocket.Conn) {
	m, _ := json.Marshal(getNums(users[ws]))
	go broadcastAll(string(m), users[ws])
}

func broadcastAll(msg string, user *userinfo) {
	connections := [4]map[*websocket.Conn]bool{
		chanels.page[user.page],
		chanels.domain[user.domain],
		chanels.rootDomain[user.rootDomain],
		chanels.world["world"],
	}

	// fmt.Println(connections)
	var err error
	for _, items := range connections {
		for conn := range items {
			if err = websocket.Message.Send(conn, msg); err != nil {
				fmt.Println("Can't send")
				onClose(conn)
			}
		}
	}
}

func onOpen(ws *websocket.Conn) {
	page := ws.Request().FormValue("from")
	id, _ := strconv.ParseInt(ws.Request().FormValue("chanel"), 10, 0)
	chanel := int8(id)
	u, err := url.Parse(ws.RemoteAddr().String())
	if err != nil {
		panic(err)
	}

	domain := strings.Split(u.Host, ":")[0]
	var rootDomain string
	domainSlice := strings.Split(domain, ".")
	if reg := regexp.MustCompile(`\.(com\.cn|com\.hk|gov\.cn|net\.cn|org\.cn)$`); reg.MatchString(domain) {
		rootDomain = strings.Join(domainSlice[len(domainSlice)-3:], ".")
	} else {
		rootDomain = strings.Join(domainSlice[len(domainSlice)-2:], ".")
	}

	if chanel < 1 || chanel > 4 {
		chanel = 3
	}

	switch chanel {
	case 1:
		if _, isExist := chanels.page[page]; !isExist {
			val := make(map[*websocket.Conn]bool)
			chanels.page[page] = val
			val[ws] = true
			nums.page[page] = &num{OnlineNum: 0, MessageNum: 0}
		} else {
			chanels.page[page][ws] = true
		}
		nums.page[page].OnlineNum++
	case 2:
		if _, isExist := chanels.domain[domain]; !isExist {
			val := make(map[*websocket.Conn]bool)
			chanels.domain[domain] = val
			val[ws] = true
			nums.domain[domain] = &num{OnlineNum: 0, MessageNum: 0}
		} else {
			chanels.domain[domain][ws] = true
		}
		nums.domain[domain].OnlineNum++
	case 3:
		if _, isExist := chanels.rootDomain[rootDomain]; !isExist {
			val := make(map[*websocket.Conn]bool)
			chanels.rootDomain[rootDomain] = val
			val[ws] = true
			nums.rootDomain[rootDomain] = &num{OnlineNum: 0, MessageNum: 0}
		} else {
			chanels.rootDomain[rootDomain][ws] = true
		}
		nums.rootDomain[rootDomain].OnlineNum++
	case 4:
		if _, isExist := chanels.world["world"]; !isExist {
			val := make(map[*websocket.Conn]bool)
			chanels.world["world"] = val
			val[ws] = true
			nums.world["world"] = &num{OnlineNum: 0, MessageNum: 0}
		} else {
			chanels.world["world"][ws] = true
		}
		nums.world["world"].OnlineNum++
	}

	users[ws] = &userinfo{curentChanel: chanel, page: page, domain: domain, rootDomain: rootDomain}
}

func onClose(ws *websocket.Conn) {
	defer delete(users, ws)
	user := users[ws]
	switch user.curentChanel {
	case 1:
		delete(chanels.page[user.page], ws)
		nums.page[user.page].OnlineNum--
	case 2:
		delete(chanels.domain[user.domain], ws)
		nums.domain[user.domain].OnlineNum--
	case 3:
		delete(chanels.rootDomain[user.rootDomain], ws)
		nums.rootDomain[user.rootDomain].OnlineNum--
	case 4:
		delete(chanels.world["world"], ws)
		nums.world["world"].OnlineNum--
	}

	broadcastNums(ws)
	fmt.Println("closed: ", ws)
}

func initChanels() {
	chanels.world = make(map[string]map[*websocket.Conn]bool)
	chanels.page = make(map[string]map[*websocket.Conn]bool)
	chanels.domain = make(map[string]map[*websocket.Conn]bool)
	chanels.rootDomain = make(map[string]map[*websocket.Conn]bool)

	nums.world = make(map[string]*num)
	nums.domain = make(map[string]*num)
	nums.rootDomain = make(map[string]*num)
	nums.page = make(map[string]*num)
}

func main() {
	initChanels()
	// go timer()
	http.Handle("/", websocket.Handler(socketHandler))

	if err := http.ListenAndServe(":12345", nil); err != nil {
		log.Fatal("ListenAndServe:", err)
	}
}

// timer to send number statistics
// func timer() {
// 	data := getNums()
// 	for {
// 		m, _ := json.Marshal(data)
// 		go broadcastAll(string(m))
// 		// fmt.Println(string(m))
// 		runtime.Gosched()
// 		time.Sleep(time.Second * 30)
// 	}
// }

func getNums(user *userinfo) interface{} {
	type numbers struct {
		World      *num
		Domain     *num
		RootDomain *num
		Page       *num
		Type       string
	}

	data := numbers{
		World:      nums.world["world"],
		Domain:     nums.domain[user.domain],
		RootDomain: nums.rootDomain[user.rootDomain],
		Page:       nums.page[user.page],
		Type:       "num",
	}

	return data
}

func substr(s string, pos, length int) string {
	runes := []rune(s)
	l := pos + length
	if l > len(runes) {
		l = len(runes)
	}
	return string(runes[pos:l])
}
