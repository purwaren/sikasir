curr=$(date +%H)
jam=$(expr $curr - 4)
jam=$(printf %02d $jam)
next=$(date +%m%d)$jam$(date +%M%Y.%S)
date $next
