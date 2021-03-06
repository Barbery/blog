package main

import (
	"code.google.com/p/go.net/websocket"
	"encoding/json"
	"fmt"
	"html"
	"log"
	"net/http"
	"net/url"
	"regexp"
	"runtime"
	"strconv"
	"strings"
	"time"
)

// var connections = make(map[*websocket.Conn]bool, 0)
var users = make(map[*websocket.Conn]*userinfo)
var chanels = new(chanel)
var nums = new(statistics)
var messages = new(history)
var maxMsgLen = 20

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

type history struct {
	world      map[string][]message
	rootDomain map[string][]message
	domain     map[string][]message
	page       map[string][]message
}

type num struct {
	OnlineNum  int8
	MessageNum int8
}

func socketHandler(ws *websocket.Conn) {
	var err error
	onOpen(ws)
	// when connect, return the nums statistics to client
	m, _ := json.Marshal(getNums())
	websocket.Message.Send(ws, string(m))

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

func parseMsg(msg string, msgType string, user *userinfo) message {
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
	return data
}

func broadcast(data message, user *userinfo) {
	var err error
	var connections map[*websocket.Conn]bool
	switch user.curentChanel {
	case 1:
		connections = chanels.page[user.page]
		messages.page[user.page] = append(messages.page[user.page], data)
		l := len(messages.page[user.page])
		min := 0
		if l-maxMsgLen > 0 {
			min = l - maxMsgLen
		}
		messages.page[user.page] = messages.page[user.page][min:l]
	case 2:
		connections = chanels.domain[user.domain]
		messages.domain[user.domain] = append(messages.domain[user.domain], data)
		l := len(messages.domain[user.domain])
		min := 0
		if l-maxMsgLen > 0 {
			min = l - maxMsgLen
		}
		messages.domain[user.domain] = messages.domain[user.domain][min:l]
	case 3:
		connections = chanels.rootDomain[user.rootDomain]
		messages.rootDomain[user.rootDomain] = append(messages.rootDomain[user.rootDomain], data)
		l := len(messages.rootDomain[user.rootDomain])
		min := 0
		if l-maxMsgLen > 0 {
			min = l - maxMsgLen
		}
		messages.rootDomain[user.rootDomain] = messages.rootDomain[user.rootDomain][min:l]
	case 4:
		connections = chanels.world["world"]
		messages.world["world"] = append(messages.world["world"], data)
		l := len(messages.world["world"])
		min := 0
		if l-maxMsgLen > 0 {
			min = l - maxMsgLen
		}
		messages.world["world"] = messages.world["world"][min:l]
	}

	m, _ := json.Marshal(data)
	msg := string(m)
	for conn := range connections {
		if err = websocket.Message.Send(conn, msg); err != nil {
			fmt.Println("Can't send")
			onClose(conn)
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
			messages.page[page] = []message{}
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
			messages.domain[domain] = []message{}
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
			messages.rootDomain[rootDomain] = []message{}
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
			messages.world["world"] = []message{}
		} else {
			chanels.world["world"][ws] = true
		}
		nums.world["world"].OnlineNum++
	}

	users[ws] = &userinfo{curentChanel: chanel, page: page, domain: domain, rootDomain: rootDomain}
	go sendHistory(users[ws], ws)
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

	messages.world = make(map[string][]message)
	messages.domain = make(map[string][]message)
	messages.rootDomain = make(map[string][]message)
	messages.page = make(map[string][]message)
}

func main() {
	initChanels()
	go timer()
	http.Handle("/", websocket.Handler(socketHandler))

	if err := http.ListenAndServe(":12345", nil); err != nil {
		log.Fatal("ListenAndServe:", err)
	}
}

// timer to send number statistics
func timer() {
	data := getNums()
	for {
		m, _ := json.Marshal(data)
		broadcastAll(string(m))
		// fmt.Println(string(m))
		runtime.Gosched()
		time.Sleep(time.Second * 60)
	}
}

func broadcastAll(msg string) {
	connections := [4]map[string]map[*websocket.Conn]bool{
		chanels.page,
		chanels.domain,
		chanels.rootDomain,
		chanels.world,
	}

	// fmt.Println(connections)
	var err error
	for _, items := range connections {
		if len(items) < 1 {
			continue
		}

		go func(items map[string]map[*websocket.Conn]bool) {
			for _, item := range items {
				for conn := range item {
					if err = websocket.Message.Send(conn, msg); err != nil {
						fmt.Println("Can't send")
						onClose(conn)
					}
				}
			}
		}(items)
	}
}

func getNums() interface{} {
	type numbers struct {
		World      map[string]*num
		Domain     map[string]*num
		RootDomain map[string]*num
		Page       map[string]*num
		Type       string
	}

	return numbers{
		World:      nums.world,
		Domain:     nums.domain,
		RootDomain: nums.rootDomain,
		Page:       nums.page,
		Type:       "num",
	}
}

func sendHistory(user *userinfo, conn *websocket.Conn) {
	switch user.curentChanel {
	case 1:
		m, _ := json.Marshal(messages.page[user.page])
		websocket.Message.Send(conn, string(m))
	case 2:
		m, _ := json.Marshal(messages.domain[user.domain])
		websocket.Message.Send(conn, string(m))
	case 3:
		m, _ := json.Marshal(messages.rootDomain[user.rootDomain])
		websocket.Message.Send(conn, string(m))
	case 4:
		m, _ := json.Marshal(messages.world["world"])
		websocket.Message.Send(conn, string(m))
	}
}

func substr(s string, pos, length int) string {
	runes := []rune(s)
	l := pos + length
	if l > len(runes) {
		l = len(runes)
	}
	return string(runes[pos:l])
}
