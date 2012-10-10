echo "Sync IP every 300 seconds"
while true
do
curl -A "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.4 (KHTML, like Gecko) Chrome/22.0.1229.79 Safari/537.4" -u "perbaungan:PhaTh4fuTUste6ar4gePacHu2uzacRAj" -d "store_code=17&name=Mode Fashion Perbaungan" -X POST http://dashboard.modefashiongroup.com/index.php/syncronizeKasir/syncIp
sleep 300
done
