#!/bin/bash
ip=$(hostname -I | awk '{print $1}'| sed -e 's/\ *$//g')
model=$(tr -d '\0' < /proc/device-tree/model)

if [ "$1" = "help" ];
	then
	echo "Use this command like so:"
	echo "		telegram <MESSAGE>."
	else
	curl --request POST \
	     --silent \
	     --output /dev/null \
	     --url https://api.telegram.org/bot5124483052%3AAAH9lPWHhj3wS-CURBEv871sw33y4DYTLM4/sendMessage \
	     --header 'Accept: application/json' \
	     --header 'Content-Type: application/json' \
	     --data "
	{
	     \"text\": \"<b><u>${HOSTNAME} - Motion Detected!</u></b>\\nMotion detected in the bedroom!!\\n\\n<i>${model%????????} - (${ip})</i>\",
	     \"chat_id\": \"2079363307\",
	     \"parse_mode\": \"html\"
	}
	"
fi
