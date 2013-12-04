local start = os.clock()
local http = require("socket.http")
local response = '';
for i=1,100000 do
    response = http.request("http://127.0.0.1:1218/?name=xoyo&opt=put&data=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa")
end
print(string.format("POST elapsed time: %.2f\n", os.clock() - start))

-- Result:
-- POST elapsed time: 25.62


start = os.clock()
for i=1,100000 do
    response = http.request("http://127.0.0.1:1218/?name=xoyo&opt=get")
end
print(string.format("GET elapsed time: %.2f\n", os.clock() - start))

-- Result:
-- GET elapsed time:25.00
