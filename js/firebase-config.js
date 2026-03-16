// Firebase Configuration and Initialization
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyBR9D0kYkYSfiFRfZQMEyidR_29NBxSqyY",
    authDomain: "petcloud-848b8.firebaseapp.com",
    projectId: "petcloud-848b8",
    storageBucket: "petcloud-848b8.firebasestorage.app",
    messagingSenderId: "924113251580",
    appId: "1:924113251580:web:657cb7a6250fcf0a7b7b79",
    measurementId: "G-XYVC0DR2G0"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Export for use in other files
export { auth };
