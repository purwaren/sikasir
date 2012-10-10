echo "Sync IP every 300 seconds"
while true
do
curl -u "perbaungan:PhaTh4fuTUste6ar4gePacHu2uzacRAj" -d "store_code=17&name=Mode Fashion Perbaungan" -X POST http://152.118.31.168/yii/app/dashboard/index.php/syncronizeKasir/syncIp > output.txt
echo "\n" > output.txt
sleep 300
done
