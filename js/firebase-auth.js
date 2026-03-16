// Firebase Authentication Functions
import { auth } from './firebase-config.js';
import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    sendPasswordResetEmail,
    signOut,
    onAuthStateChanged
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

// Sign Up Function
export async function signUp(email, password, fullName) {
    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        // Store user info in localStorage for quick access
        localStorage.setItem('petcloud_user', JSON.stringify({
            name: fullName,
            email: userCredential.user.email,
            picture: `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=random`
        }));
        return { success: true, user: userCredential.user };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Login Function
export async function login(email, password) {
    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        // Get user name from localStorage or use email
        const storedUser = localStorage.getItem('petcloud_user');
        let userName = email.split('@')[0];

        if (storedUser) {
            const parsed = JSON.parse(storedUser);
            userName = parsed.name || userName;
        }

        localStorage.setItem('petcloud_user', JSON.stringify({
            name: userName,
            email: userCredential.user.email,
            picture: `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`
        }));

        return { success: true, user: userCredential.user };
    } catch (error) {
        let friendlyMessage = 'Invalid email or password.';
        if (error.code === 'auth/user-not-found') friendlyMessage = 'No account found with this email.';
        if (error.code === 'auth/wrong-password') friendlyMessage = 'Incorrect password.';
        if (error.code === 'auth/invalid-email') friendlyMessage = 'Invalid email address.';
        return { success: false, error: friendlyMessage };
    }
}

// Forgot Password Function
export async function resetPassword(email) {
    try {
        await sendPasswordResetEmail(auth, email);
        return { success: true };
    } catch (error) {
        let friendlyMessage = 'Failed to send reset email.';
        if (error.code === 'auth/user-not-found') friendlyMessage = 'No account found with this email.';
        if (error.code === 'auth/invalid-email') friendlyMessage = 'Invalid email address.';
        return { success: false, error: friendlyMessage };
    }
}

// Logout Function
export async function logout() {
    try {
        await signOut(auth);
        localStorage.removeItem('petcloud_user');
        return { success: true };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Google Sign-In Function
export async function signInWithGoogle() {
    const { GoogleAuthProvider, signInWithPopup } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js");

    try {
        const provider = new GoogleAuthProvider();
        const result = await signInWithPopup(auth, provider);

        // Store user info
        localStorage.setItem('petcloud_user', JSON.stringify({
            name: result.user.displayName || result.user.email.split('@')[0],
            email: result.user.email,
            picture: result.user.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(result.user.displayName || result.user.email)}&background=random`
        }));

        return { success: true, user: result.user };
    } catch (error) {
        let friendlyMessage = 'Google Sign-In failed.';
        if (error.code === 'auth/popup-closed-by-user') friendlyMessage = 'Sign-in cancelled.';
        if (error.code === 'auth/popup-blocked') friendlyMessage = 'Please allow popups for this site.';
        return { success: false, error: friendlyMessage };
    }
}

// Check Auth State (for protecting dashboard)
export function checkAuthState(callback) {
    onAuthStateChanged(auth, (user) => {
        callback(user);
    });
}

// Protect Page (redirect if not logged in)
export function protectPage() {
    onAuthStateChanged(auth, (user) => {
        if (!user) {
            window.location.href = 'index.html';
        }
    });
}
