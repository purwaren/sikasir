echo "UPDATE JAM SERVER"
echo "Sekarang: "$(date +%H:%M:%S)
echo "Mundurkan berapa jam ?" 
read diff
curr=$(date +%H)
jam=$(expr $curr - $diff)
jam=$(printf %02d $jam)
next=$(date +%m%d)$jam$(date +%M%Y.%S)
echo "next: "$next
echo "Anda akan memundurkan jam sebanyak "$diff" jam"
read -p "Apakah anda yakin ? Y/n" -n 1 -r
if [[ $REPLY =~ ^[Yy]$ ]]
then
    # do dangerous stuff
	sudo date $next
	echo $'\n'"Berhasil"
else
	echo $'\n'"Operasi dibatalkan"
fi