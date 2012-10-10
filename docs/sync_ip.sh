echo "Sync IP every 300 seconds"
while true
do
curl -u "perbaungan:PhaTh4fuTUste6ar4gePacHu2uzacRAj" -d "store_code=17&name=Mode Fashion Perbaungan" -X POST http://dashboard.modefashiongroup.com/index.php/syncronizeKasir/syncIp
sleep 300
done
