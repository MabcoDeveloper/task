var firebaseConfig = {
    apiKey: "AIzaSyBxIF6uKq0xfBGCDSYvJZ1AYii05I_y-Jk",
    authDomain: "mabco-osticket.firebaseapp.com",
    databaseURL: "https://mabco-osticket.firebaseio.com",
    projectId: "mabco-osticket",
    storageBucket: "mabco-osticket.appspot.com",
    messagingSenderId: "937804783527",
    appId: "1:937804783527:web:da6cc54ca9b0d72c9e6082",
    measurementId: "G-RSKNL2TFTM"
};

firebase.initializeApp(firebaseConfig);
firebase.analytics();

const messaging = firebase.messaging();

var StaffOrUser = '';

$(window).on("load", function () {
    if (window.location.href.includes('scp')) {
        StaffOrUser = 'staff';
    } else {
        StaffOrUser = 'user';
    }

    AskForNotificationsPermission(StaffOrUser);
});

$(document).on('click', '#allow_notifications_but', function () {
    AskForNotificationsPermission(StaffOrUser);
});

function AskForNotificationsPermission(StaffOrUser) {
	if (window.Notification && Notification.permission !== "granted") {
		Notification.requestPermission().then(function (status) {
			if (Notification.permission !== status) {
				Notification.permission = status;
            }
            
            if (status === "granted") {
                GetNotificationsToken(StaffOrUser);
            }
		});	
	} else {
        GetNotificationsToken(StaffOrUser);
    }
}

function GetNotificationsToken(StaffOrUser) {
    messaging.getToken().then((currentToken) => {
        if (currentToken) {
            SendTokenToServer(currentToken, StaffOrUser);
        } else {
            console.log('No Instance ID token available. Request permission to generate one.');
            AskForNotificationsPermission();
        }
    }).catch((err) => {
        console.log('An error occurred while retrieving token. ', err);
    });

    messaging.onTokenRefresh(() => {
        messaging.getToken().then((refreshedToken) => {
            console.log('Token refreshed.');
            SendTokenToServer(refreshedToken, StaffOrUser);
        }).catch((err) => {
            console.log('Unable to retrieve refreshed token ', err);
        });
      });
}

function SendTokenToServer(Token, StaffOrUser) {
    var RequestURL = '';
    var RequestClickActionURL = '/scp/tasks.php';

    if (StaffOrUser === 'staff') {
        RequestURL = 'ajax.php/staff/setFCMToken';
    } else {
        RequestURL = 'ajax.php/users/setFCMToken';
        RequestClickActionURL = '/task/tickets.php';
    }
    
    $.ajax({
        type: "POST",
        url: RequestURL,
        data: { token: Token },
        datatype: 'json',
        success: function(response) {
            if (hasJsonStructure(response)) {
                response = JSON.parse(response);
            }
        
            if (response['success']) {
                var RequestSettings = {
                    "url": "https://fcm.googleapis.com/fcm/send",
                    "method": "POST",
                    "timeout": 0,
                    "headers": {
                        "Authorization": "key=AAAA2lmFX6c:APA91bFLMhlYHnrm_634cD8rmG1SXTDKQX7dBTBygj2WZQWXiExSfBTWNAbkgnjAwveJ9BlFEkMdjDm58TvlJd3IfjEaHa32LLGDeX7B8p0UW6Nem1xxqR_1XiadMm_EFWo5EQP8BCNr",
                        "Content-Type": "application/json"
                    },
                    "data": JSON.stringify({"to":response['token'],"content_available":false,"data":{"notification":{"title":response[StaffOrUser + '_name'],"body":"تم تفعيل الاشعارات بنجاح"},"click_action":RequestClickActionURL}}),
                };
                
                $.ajax(RequestSettings);
            }
        }
    });
}

function hasJsonStructure(str) {
    if (typeof str !== 'string') return false;
    try {
        const result = JSON.parse(str);
        const type = Object.prototype.toString.call(result);
        return type === '[object Object]' 
            || type === '[object Array]';
    } catch (err) {
        return false;
    }
}

messaging.onMessage((payload) => {
    var NotificationActionURL = payload.data.click_action;

    // let timerInterval
    // Swal.fire({
    //     position: 'bottom-end',
    //     title: JSON.parse(payload.data.notification)['title'],
    //     html: JSON.parse(payload.data.notification)['body'],
    //     confirmButtonText: 'Open',
    //     timer: 10000,
    //     heightAuto: false,
    //     width: 350,
    //     timerProgressBar: true,
    //     backdrop: false,
    //     onClose: () => {
    //         clearInterval(timerInterval);
    //     }
    // }).then((result) => {
    //     if (result.dismiss === Swal.DismissReason.timer){
    //         console.log('I was closed by the timer')
    //     }
        
    //     if (result.value){
    //        window.open(NotificationActionURL, '_blank').focus();
    //     }
    // });

    var notification = new Notification(JSON.parse(payload.data.notification)['title'], {
        icon: '/task/images/favicon.gif',
        body: JSON.parse(payload.data.notification)['body'],
    });

    notification.onclick = function() {
        window.open(NotificationActionURL, '_blank').focus();
    };
});