importScripts("https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js");

firebase.initializeApp({
apiKey: "AIzaSyApvYW8PI_Deilq2-6rnawiHKP26qTVZ7g",
  authDomain: "lpkmobile-efeba.firebaseapp.com",
  projectId: "lpkmobile-efeba",
  storageBucket: "lpkmobile-efeba.appspot.com",
  messagingSenderId: "636656161904",
  appId: "1:636656161904:web:8fe7989eea80b6bca890b5",
  measurementId: "G-07XGTBQ75H"
});

const messaging = firebase.messaging();

// Optional:
messaging.onBackgroundMessage((message) => {
  console.log("onBackgroundMessage", message);
});