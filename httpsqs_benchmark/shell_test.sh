max=1000
i=0
now=$(date +'%s')
while [ $i -lt $max ]
do
    data=$(curl -s "http://127.0.0.1:1218/?name=xoyo&opt=get")
    i=$(expr $i + 1)
done
end=$(date +'%s')
echo $(expr $end - $now|bc)

# shell is worse performance, be careful the i and max number, 1000 times, takes 12s
# I can't believe it, is my code have bug?
