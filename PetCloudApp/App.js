import { StatusBar } from 'expo-status-bar';
import React, { useState, useEffect, useRef } from 'react';
import { StyleSheet, Text, View, TextInput, TouchableOpacity, ScrollView, Alert, Image, RefreshControl, Dimensions, ActivityIndicator, Modal, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

import * as ImagePicker from 'expo-image-picker';

import { API_URL, API_BASE_URL, getImageUrl, fetchWithTimeout } from './config';

export default function App() {
    const [screen, setScreen] = useState('login');
    const [user, setUser] = useState(null);
    const mainScrollRef = useRef(null);

    // Profile State
    const [profileData, setProfileData] = useState({ full_name: '', phone: '', location: '', bio: '', profile_image: null });
    const [profileLoading, setProfileLoading] = useState(false);
    const [profileSaving, setProfileSaving] = useState(false);

    // Adoption Form State
    const [isAdoptionFormVisible, setIsAdoptionFormVisible] = useState(false);
    const [adoptionFormData, setAdoptionFormData] = useState({ phone: '', reason: '', living_situation: '', other_pets: false });
    const [adoptionSubmitting, setAdoptionSubmitting] = useState(false);

    // Health Records State
    const [healthRecords, setHealthRecords] = useState([]);
    const [healthLoading, setHealthLoading] = useState(false);
    const [symptomChat, setSymptomChat] = useState([]);
    const [symptomInput, setSymptomInput] = useState('');
    const [symptomAnalyzing, setSymptomAnalyzing] = useState(false);

    // Adoption Applications State
    const [adoptionApplications, setAdoptionApplications] = useState([]);
    const [adoptionStatusLoading, setAdoptionStatusLoading] = useState(false);
    const [adoptionView, setAdoptionView] = useState('listings'); // 'listings' or 'my_applications'

    // Add Health Record State
    const [isAddHealthModalVisible, setIsAddHealthModalVisible] = useState(false);
    const [newHealthData, setNewHealthData] = useState({ pet_id: '', record_type: 'Vaccination', date: new Date().toISOString().split('T')[0], title: '', description: '' });
    const [healthSaving, setHealthSaving] = useState(false);

    // Dashboard Data
    const [dashboardData, setDashboardData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [refreshing, setRefreshing] = useState(false);

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [fullName, setFullName] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [isPasswordResetMode, setIsPasswordResetMode] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);
    const [menuVisible, setMenuVisible] = useState(false);
    const [activeItem, setActiveItem] = useState('Overview');

    // Adoption Screen State
    const [adoptionListings, setAdoptionListings] = useState([]);
    const [adoptionLoading, setAdoptionLoading] = useState(false);
    const [selectedCategory, setSelectedCategory] = useState('All Pets');

    // My Pets State
    const [userPets, setUserPets] = useState([]);
    const [petsLoading, setPetsLoading] = useState(false);

    // Marketplace State
    const [products, setProducts] = useState([]);
    const [productsLoading, setProductsLoading] = useState(false);
    const [marketplaceSearch, setMarketplaceSearch] = useState('');
    const [marketplaceCategory, setMarketplaceCategory] = useState('All');

    // Cart & Checkout State
    const [cartItems, setCartItems] = useState([]);
    const [cartTotal, setCartTotal] = useState(0);
    const [isCartVisible, setIsCartVisible] = useState(false);
    const [isCheckoutVisible, setIsCheckoutVisible] = useState(false);
    const [isAddPetModalVisible, setIsAddPetModalVisible] = useState(false);
    const [newPetData, setNewPetData] = useState({ 
        name: '', 
        breed: '', 
        gender: 'Male', 
        age: '1 Year',
        type: 'Dog',
        weight: '',
        description: '',
        image: null 
    });
    const [checkoutStep, setCheckoutStep] = useState(1); // 1: Shipping, 2: Payment, 3: Success
    const [paymentSubStep, setPaymentSubStep] = useState('Splash'); // Splash, Contact, Methods
    const [selectedMethod, setSelectedMethod] = useState('Cards'); // Cards, Netbanking, Wallet, PayLater
    const [selectedBank, setSelectedBank] = useState(null);
    const [shippingDetails, setShippingDetails] = useState({
        name: '',
        address: '',
        city: '',
        zip: '',
        phone: ''
    });

    // My Orders State
    const [myOrders, setMyOrders] = useState([]);
    const [ordersLoading, setOrdersLoading] = useState(false);

    // Smart Feeder State
    const [feederStatus, setFeederStatus] = useState('Online');
    const [selectedPortion, setSelectedPortion] = useState('Medium'); // Small, Medium, Large
    const [feederData, setFeederData] = useState({ last_feed: { time: '--', portion: '--' }, history: [], schedules: [] });
    const [feederLoading, setFeederLoading] = useState(false);
    const [selectedFeederPetId, setSelectedFeederPetId] = useState(null);
    const [scheduleTime, setScheduleTime] = useState('');
    const [schedulePortion, setSchedulePortion] = useState('40');

    // Schedule State
    const [scheduleData, setScheduleData] = useState({
        pet_name: '',
        breed: 'Dog',
        service_id: null
    });
    const [estimation, setEstimation] = useState(0);

    const scheduleCategories = [
        { id: 1, title: 'Medical Consultation', icon: 'medical-outline', price: 500 },
        { id: 2, title: 'Preventive Care', icon: 'shield-checkmark-outline', price: 300 },
        { id: 3, title: 'Grooming & Spa', icon: 'water-outline', price: 800 },
        { id: 4, title: 'Diagnostics', icon: 'flask-outline', price: 1200 },
        { id: 5, title: 'Surgery & Dental', icon: 'bandage-outline', price: 2500 },
        { id: 6, title: 'Alternative Therapy', icon: 'leaf-outline', price: 1000 },
        { id: 7, title: 'Training & Behavior', icon: 'school-outline', price: 1500 },
        { id: 8, title: 'Boarding & Daycare', icon: 'home-outline', price: 1000 },
        { id: 9, title: 'Pet Transportation', icon: 'car-outline', price: 600 },
        { id: 10, title: 'Walking & Sitting', icon: 'walk-outline', price: 400 },
        { id: 11, title: 'Pet Photography', icon: 'camera-outline', price: 2000 },
        { id: 12, title: 'Insurance & Legal', icon: 'document-text-outline', price: 500 },
    ];

    // Advanced Schedule Flow State
    const [scheduleStep, setScheduleStep] = useState(1);
    const [availableServices, setAvailableServices] = useState([]);
    const [availableHospitals, setAvailableHospitals] = useState([]);
    const [availableSlots, setAvailableSlots] = useState([]);
    const [selectedSubService, setSelectedSubService] = useState(null);
    const [selectedHospital, setSelectedHospital] = useState(null);
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [selectedTime, setSelectedTime] = useState(null);
    const [scheduleLoading, setScheduleLoading] = useState(false);

    // Payment State
    const [paymentModalVisible, setPaymentModalVisible] = useState(false);
    const [paymentProcessing, setPaymentProcessing] = useState(false);
    const [paymentSuccess, setPaymentSuccess] = useState(false);

    // Pet Details Modal State
    const [selectedPet, setSelectedPet] = useState(null);
    const [isPetModalVisible, setIsPetModalVisible] = useState(false);

    // My Rehoming Listings
    const [myRehomingListings, setMyRehomingListings] = useState([]);
    const [rehomingView, setRehomingView] = useState('list'); // 'list' or 'form'
    const [isListingsLoading, setIsListingsLoading] = useState(false);

    const handleLogin = async () => {
        if (!email || !password) {
            Alert.alert('Missing Info', 'Please enter your email and password.');
            return;
        }

        setIsProcessing(true);
        console.log(`Connecting to: ${API_URL}/login.php`);

        try {
            const response = await fetchWithTimeout(`${API_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server. Please check if PHP API is returning valid JSON.');
            }

            console.log('Login Result:', data);

            if (data.success) {
                if (data.user.role !== 'client') {
                    Alert.alert('Access Denied', 'This app is only for Pet Owners. Please use the web portal for Shop Owner/Admin access.');
                    setIsProcessing(false);
                    return;
                }
                setUser(data.user);
                setScreen('dashboard');
                fetchDashboardData(data.user.id);
            } else {
                Alert.alert('Login Failed', data.error || 'Invalid email or password.');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            Alert.alert('Connection Error',
                error.message + '\n\n' +
                'Troubleshooting:\n' +
                '1. Ensure XAMPP (Apache & MySQL) is running.\n' +
                '2. Verify your Phone and PC are on the SAME Wi-Fi.\n' +
                '3. Check PC IP in config.js (Current: ' + API_URL + ')\n' +
                '4. Ensure Firewall is not blocking port 80.'
            );
        } finally {
            setIsProcessing(false);
        }
    };

    const handleSignup = async () => {
        if (!fullName || !email || !password || !confirmPassword) {
            Alert.alert('Missing Info', 'Please fill in all fields.');
            return;
        }
        if (password !== confirmPassword) {
            Alert.alert('Error', 'Passwords do not match.');
            return;
        }

        setIsProcessing(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ full_name: fullName, email, password }),
            });

            const data = await response.json();
            if (data.success) {
                Alert.alert('Success', 'Account created! Welcome to PetCloud.');
                setUser(data.user);
                setScreen('dashboard');
                fetchDashboardData(data.user.id);
            } else {
                Alert.alert('Signup Failed', data.error || 'Could not create account.');
            }
        } catch (error) {
            console.error('Signup Error:', error);
            Alert.alert('Error', 'Registration search failed. Check your connection.');
        } finally {
            setIsProcessing(false);
        }
    };

    const handleForgotPassword = async () => {
        if (!email) {
            Alert.alert('Error', 'Please enter your email address first.');
            return;
        }

        setIsProcessing(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/forgot_password.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email }),
            });

            const data = await response.json();
            if (data.success) {
                Alert.alert('Reset Sent', 'Please check your email for password reset instructions.');
                setIsPasswordResetMode(false);
            } else {
                Alert.alert('Error', data.error || 'Failed to send reset email.');
            }
        } catch (error) {
            console.error('Reset Error:', error);
            Alert.alert('Error', 'Could not reach server.');
        } finally {
            setIsProcessing(false);
        }
    };

    const fetchDashboardData = async (userId) => {
        setLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_dashboard.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            const data = await response.json();
            setDashboardData(data);
        } catch (error) {
            console.error('Dashboard Error:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const onRefresh = () => {
        setRefreshing(true);
        if (user) fetchDashboardData(user.id);
    };

    const handleLogout = () => {
        setUser(null);
        setScreen('login');
        setEmail('');
        setPassword('');
        setDashboardData(null);
        setMyOrders([]);
        setCartItems([]);
        setUserPets([]);
        setActiveItem('Overview');
    };

    const fetchUserPets = async () => {
        if (!user) return;
        setPetsLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_pets.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.success) setUserPets(data.data);
        } catch (error) {
            console.error("Pets Fetch Error:", error);
        } finally {
            setPetsLoading(false);
        }
    };

    const fetchMarketplace = async (category = 'All', search = '') => {
        setProductsLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_marketplace.php?category=${category}&search=${search}`);
            const data = await response.json();
            if (data.success) setProducts(data.data);
        } catch (error) {
            console.error("Marketplace Fetch Error:", error);
        } finally {
            setProductsLoading(false);
        }
    };

    const fetchCart = async () => {
        if (!user) return;
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_cart.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.success) {
                setCartItems(data.data);
                setCartTotal(data.total);
            }
        } catch (error) {
            console.error("Cart Fetch Error:", error);
        }
    };

    const handleAddToCart = async (productId) => {
        if (!user) return;
        try {
            const response = await fetchWithTimeout(`${API_URL}/add_to_cart.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: user.id, product_id: productId })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Item added to cart! 🛒");
                fetchCart();
            }
        } catch (error) {
            console.error("Add to Cart Error:", error);
        }
    };

    const handleRemoveFromCart = async (productId) => {
        if (!user) return;
        try {
            const response = await fetchWithTimeout(`${API_URL}/delete_cart_item.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: user.id, product_id: productId })
            });
            const data = await response.json();
            if (data.success) {
                fetchCart();
            }
        } catch (error) {
            console.error("Remove from Cart Error:", error);
        }
    };

    const fetchMyOrders = async () => {
        if (!user) return;
        setOrdersLoading(true);
        console.log(`[Orders] Fetching for user: ${user.id}`);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_dashboard.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: user.id })
            });
            const data = await response.json();
            console.log("[Orders] Received:", data.orders?.length || 0, "items");
            if (data.orders) setMyOrders(data.orders);
        } catch (error) {
            console.error("Orders Fetch Error:", error);
        } finally {
            setOrdersLoading(false);
        }
    };

    const handlePlaceOrder = async () => {
        if (!user) return;
        if (!shippingDetails.address || !shippingDetails.city || !shippingDetails.zip) {
            Alert.alert("Missing Info", "Please fill in all shipping details.");
            return;
        }

        setIsProcessing(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/place_order.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: user.id,
                    total_amount: cartTotal,
                    shipping_details: shippingDetails,
                    payment_method: selectedMethod === 'Netbanking' ? `Netbanking (${selectedBank || 'All Banks'})` : (selectedMethod || 'Card'),
                    payment_id: 'MOB_' + Math.random().toString(36).substr(2, 9).toUpperCase()
                })
            });
            const data = await response.json();
            if (data.success) {
                setCheckoutStep(3); // Success Screen
                fetchCart();
                fetchMyOrders(); // NEW: Explicitly refresh orders list
            } else {
                Alert.alert("Error", data.error || "Failed to place order.");
            }
        } catch (error) {
            console.error("Place Order Error:", error);
            Alert.alert("Error", "Could not complete order. Please check your connection.");
        } finally {
            setIsProcessing(false);
        }
    };

    const handleMarkFound = async (petId) => {
        if (!user) return;
        setIsProcessing(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/mark_as_found.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: user.id, pet_id: petId })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Wonderful news! Your pet is marked as safe. 🎉");
                fetchDashboardData(user.id);
                fetchUserPets();
            } else {
                Alert.alert("Error", data.error || "Failed to update status.");
            }
        } catch (error) {
            console.error(error);
            Alert.alert("Error", "Check your connection.");
        } finally {
            setIsProcessing(false);
        }
    };

    const fetchFeederData = async () => {
        if (!user) return;
        setFeederLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_feeder_data.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.ok) {
                setFeederData(data);
                if (data.history && data.history.length > 0 && !selectedFeederPetId) {
                    setSelectedFeederPetId(data.history[0].pet_id);
                } else if (userPets.length > 0 && !selectedFeederPetId) {
                    setSelectedFeederPetId(userPets[0].id);
                }
            }
        } catch (error) {
            console.error("Feeder Fetch Error:", error);
        } finally {
            setFeederLoading(false);
        }
    };

    const handleManualFeed = async () => {
        if (!user || !selectedFeederPetId) {
            Alert.alert("Error", "Please select a pet first.");
            return;
        }

        let qty = 30;
        if (selectedPortion === 'Medium') qty = 60;
        if (selectedPortion === 'Large') qty = 100;

        try {
            const params = new URLSearchParams();
            params.append('user_id', user.id);
            params.append('pet_id', selectedFeederPetId);
            params.append('quantity', qty);

            const response = await fetchWithTimeout(`${API_URL}/trigger_manual_feed.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });
            const data = await response.json();
            if (data.ok) {
                Alert.alert("Success", `Dispensing ${qty}g for your pet!`);
                fetchFeederData();
            } else {
                Alert.alert("Error", data.error || "Failed to feed.");
            }
        } catch (error) {
            console.error(error);
            Alert.alert("Error", "Check your connection.");
        }
    };

    // Advanced Scheduling Logic
    const fetchServicesByCategory = async (catId) => {
        setScheduleLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_BASE_URL}/api/get_services.php?category_id=${catId}`);
            const data = await response.json();
            if (data.success) {
                setAvailableServices(data.data);
                // setScheduleStep(2); // Keep on Step 1 to show services below
            } else {
                Alert.alert("Error", "Could not load services.");
            }
        } catch (error) {
            console.error("Fetch Services Error:", error);
        } finally {
            setScheduleLoading(false);
        }
    };

    const fetchHospitalsByService = async (serviceName) => {
        setScheduleLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_BASE_URL}/api_get_hospitals.php?service=${encodeURIComponent(serviceName)}`);
            const data = await response.json();
            if (Array.isArray(data)) {
                setAvailableHospitals(data);
                // setScheduleStep(3); // Keep on same page
            } else {
                Alert.alert("Error", "Could not load clinics.");
            }
        } catch (error) {
            console.error("Fetch Hospitals Error:", error);
        } finally {
            setScheduleLoading(false);
        }
    };

    const fetchSlots = async (hospId, date) => {
        setScheduleLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_BASE_URL}/api_get_slots.php?hospital_id=${hospId}&date=${date}`);
            const data = await response.json();
            if (Array.isArray(data)) {
                setAvailableSlots(data);
                // setScheduleStep(4); // Keep on same page
            } else {
                Alert.alert("Error", "Could not load available slots.");
            }
        } catch (error) {
            console.error("Fetch Slots Error:", error);
        } finally {
            setScheduleLoading(false);
        }
    };

    const handleProcessPayment = async () => {
        if (!user) return;
        setPaymentProcessing(true);

        try {
            const payment_id = 'MOB_APPT_' + Math.random().toString(36).substr(2, 9).toUpperCase();
            const payment_method = selectedMethod === 'Netbanking' ? `Netbanking (${selectedBank || 'All Banks'})` : (selectedMethod || 'Card');

            const response = await fetchWithTimeout(`${API_URL}/book_appointment.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: user.id,
                    hospital_id: selectedHospital?.id,
                    appointment_date: selectedDate,
                    appointment_time: selectedTime,
                    pet_name: scheduleData.pet_name,
                    breed: scheduleData.breed,
                    service_type: selectedSubService || availableServices[0]?.name || 'General',
                    payment_id: payment_id,
                    payment_method: payment_method,
                    cost: selectedHospital?.price || estimation
                })
            });
            const data = await response.json();

            if (data.success) {
                setPaymentSuccess(true);
                setTimeout(() => {
                    setPaymentModalVisible(false);
                    setPaymentSuccess(false);
                    setScheduleStep(1);
                    setActiveItem('Overview');
                    onRefresh();
                    Alert.alert("Success", "Appointment booked and payment verified!");
                }, 2000);
            } else {
                Alert.alert("Error", data.error || "Failed to book appointment.");
            }
        } catch (error) {
            console.error("Payment Process Error:", error);
            Alert.alert("Error", "Payment failed or connection lost.");
        } finally {
            setPaymentProcessing(false);
        }
    };

    const handleSaveSchedule = async () => {
        if (!user || !selectedFeederPetId || !scheduleTime) {
            Alert.alert("Error", "Please select a pet and time.");
            return;
        }

        try {
            const params = new URLSearchParams();
            params.append('user_id', user.id);
            params.append('pet_id', selectedFeederPetId);
            params.append('feeding_time', scheduleTime);
            params.append('quantity', schedulePortion);

            const response = await fetchWithTimeout(`${API_URL}/save_feeder_schedule.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });
            const data = await response.json();
            if (data.ok) {
                Alert.alert("Success", "Feeding schedule saved.");
                fetchFeederData();
                setScheduleTime('');
            } else {
                Alert.alert("Error", data.error || "Failed to save schedule.");
            }
        } catch (error) {
            console.error(error);
            Alert.alert("Error", "Check your connection.");
        }
    };



    const handleReportLost = async (petId) => {
        if (!user) return;

        Alert.prompt(
            "Report Lost",
            "Where was your pet last seen?",
            [
                { text: "Cancel", style: "cancel" },
                {
                    text: "Report",
                    onPress: async (location) => {
                        if (!location) return;
                        setIsProcessing(true);
                        try {
                            const response = await fetchWithTimeout(`${API_URL}/report_lost.php`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    user_id: user.id,
                                    pet_id: petId,
                                    location: location,
                                    date: new Date().toISOString().split('T')[0]
                                })
                            });
                            const data = await response.json();
                            if (data.success) {
                                Alert.alert("Alert Triggered", "Your pet has been marked as lost and nearby users have been notified.");
                                fetchUserPets();
                                fetchDashboardData(user.id);
                            } else {
                                Alert.alert("Error", data.error || "Failed to report.");
                            }
                        } catch (e) {
                            Alert.alert("Error", "Connection failed.");
                        } finally {
                            setIsProcessing(false);
                        }
                    }
                }
            ],
            "plain-text"
        );
    };

    const handleAddPet = async () => {
        if (!newPetData.name) {
            Alert.alert("Error", "Pet name is required.");
            return;
        }
        setIsProcessing(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/add_pet.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: user.id,
                    pet_name: newPetData.name,
                    pet_breed: newPetData.breed,
                    pet_gender: newPetData.gender,
                    pet_age: newPetData.age,
                    pet_type: newPetData.type,
                    pet_weight: newPetData.weight,
                    pet_description: newPetData.description,
                    pet_image: newPetData.image || 'uploads/pets/default.png'
                })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "New family member added! ❤️");
                setIsAddPetModalVisible(false);
                setNewPetData({ 
                    name: '', 
                    breed: '', 
                    gender: 'Male', 
                    age: '1 Year',
                    type: 'Dog',
                    weight: '',
                    description: '',
                    image: null
                });
                fetchUserPets();
            } else {
                Alert.alert("Error", data.error || "Failed to add pet.");
            }
        } catch (e) {
            Alert.alert("Error", "Connection failed.");
        } finally {
            setIsProcessing(false);
        }
    };

    const handleDeletePet = async (petId) => {
        try {
            const response = await fetchWithTimeout(`${API_URL}/delete_pet.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pet_id: petId, user_id: user.id })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Pet removed from your family.");
                fetchUserPets();
            } else {
                Alert.alert("Error", data.error || "Failed to delete.");
            }
        } catch (e) {
            console.error(e);
        }
    };

    const fetchMyRehomingListings = async () => {
        if (!user) return;
        setIsListingsLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_my_rehoming_listings.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.success) {
                setMyRehomingListings(data.data || []);
            }
        } catch (error) {
            console.error("Rehoming Listings Error:", error);
        } finally {
            setIsListingsLoading(false);
        }
    };

    const fetchProfile = async () => {
        if (!user) return;
        setProfileLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_profile.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.success) {
                setProfileData({
                    full_name: data.data.full_name || '',
                    phone: data.data.phone || '',
                    location: data.data.location || '',
                    bio: data.data.bio || '',
                    profile_image: data.data.profile_image || null
                });
            }
        } catch (error) {
            console.error("Profile Fetch Error:", error);
        } finally {
            setProfileLoading(false);
        }
    };

    const handlePickImage = async () => {
        const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (status !== 'granted') {
            Alert.alert('Permission Denied', 'Sorry, we need camera roll permissions to make this work!');
            return;
        }

        let result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ImagePicker.MediaTypeOptions.Images,
            allowsEditing: true,
            aspect: [1, 1],
            quality: 0.5,
            base64: true,
        });

        if (!result.canceled) {
            setProfileData({ ...profileData, profile_image: `data:image/jpeg;base64,${result.assets[0].base64}` });
        }
    };

    const handleUpdateProfile = async () => {
        if (!user) return;
        if (!profileData.full_name) {
            Alert.alert("Error", "Full Name is required.");
            return;
        }
        setProfileSaving(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/update_profile.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...profileData, user_id: user.id })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Profile updated successfully!");
                const updatedUser = { ...user, full_name: profileData.full_name };
                setUser(updatedUser);
            } else {
                Alert.alert("Error", data.error || "Failed to update profile.");
            }
        } catch (error) {
            console.error("Profile Update Error:", error);
            Alert.alert("Error", "Could not connect to the server.");
        } finally {
            setProfileSaving(false);
        }
    };

    const handleApplyAdoption = async () => {
        if (!user || !selectedPet) return;
        if (!adoptionFormData.phone || !adoptionFormData.reason || !adoptionFormData.living_situation) {
            Alert.alert("Error", "Please fill in all required fields.");
            return;
        }

        setAdoptionSubmitting(true);
        try {
            const payload = {
                user_id: user.id,
                listing_id: selectedPet.listing_id || null,
                pet_name: selectedPet.pet_name,
                pet_category: selectedPet.pet_type,
                full_name: profileData.full_name || user.full_name || user.email,
                email: user.email,
                phone: adoptionFormData.phone,
                reason: adoptionFormData.reason,
                living_situation: adoptionFormData.living_situation,
                other_pets: adoptionFormData.other_pets ? 1 : 0
            };

            const response = await fetchWithTimeout(`${API_URL}/apply_adoption.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Your adoption application has been submitted! We will contact you soon.");
                setIsAdoptionFormVisible(false);
                setAdoptionFormData({ phone: '', reason: '', living_situation: '', other_pets: false });
            } else {
                Alert.alert("Error", data.error || "Failed to submit application.");
            }
        } catch (error) {
            console.error("Adoption Submit Error:", error);
            Alert.alert("Error", "Could not connect to the server.");
        } finally {
            setAdoptionSubmitting(false);
        }
    };

    const handleDeleteRehomingListing = async (listingId) => {
        try {
            const response = await fetchWithTimeout(`${API_URL}/delete_rehoming_listing.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ listing_id: listingId, user_id: user.id })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Listing deleted.");
                fetchMyRehomingListings();
            } else {
                Alert.alert("Error", data.error || "Failed to delete.");
            }
        } catch (error) {
            console.error(error);
        }
    };

    useEffect(() => {
        if (!user) return;

        if (activeItem === 'Adoption') {
            const categories = [
                { id: null, name: 'All Pets' },
                { id: 1, name: 'Dogs' },
                { id: 2, name: 'Cats' },
                { id: 3, name: 'Rabbits' },
                { id: 4, name: 'Birds' }
            ];
            const cat = categories.find(c => c.name === selectedCategory);
            fetchAdoptionListings(cat?.id);
        } else if (activeItem === 'My Pets') {
            fetchUserPets();
        } else if (activeItem === 'Marketplace') {
            fetchMarketplace(marketplaceCategory, marketplaceSearch);
            fetchCart();
        } else if (activeItem === 'My Orders') {
            fetchMyOrders();
        } else if (activeItem === 'Pet Rehoming') {
            fetchMyRehomingListings();
            setRehomingView('list');
        } else if (activeItem === 'Smart Feeder') {
            fetchFeederData();
            if (userPets.length === 0) fetchUserPets();
        } else if (activeItem === 'Profile') {
            fetchProfile();
        } else if (activeItem === 'Health') {
            fetchHealthRecords();
        } else if (activeItem === 'Adoption' && adoptionView === 'my_applications') {
            fetchAdoptionStatus();
        }
    }, [activeItem, adoptionView, selectedCategory, user]);

    const fetchAdoptionStatus = async () => {
        if (!user) return;
        setAdoptionStatusLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_adoption_status.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.success) {
                setAdoptionApplications(data.applications);
            }
        } catch (error) {
            console.error("Adoption Status Fetch Error", error);
        } finally {
            setAdoptionStatusLoading(false);
        }
    };

    const handleSymptomCheck = async () => {
        if (!symptomInput.trim()) return;
        
        const newChat = [...symptomChat, { type: 'user', text: symptomInput }];
        setSymptomChat(newChat);
        setSymptomAnalyzing(true);
        
        const currentInput = symptomInput;
        setSymptomInput('');

        try {
            const response = await fetchWithTimeout(`${API_URL}/symptom_checker.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ symptom: currentInput })
            });
            const data = await response.json();
            if (data.success) {
                setSymptomChat([...newChat, { type: 'bot', text: data.response }]);
            } else {
                setSymptomChat([...newChat, { type: 'bot', text: data.error || "Sorry, I couldn't analyze that symptom right now." }]);
            }
        } catch (error) {
            console.error("Symptom API Error", error);
            setSymptomChat([...newChat, { type: 'bot', text: "Error connecting to AI service." }]);
        } finally {
            setSymptomAnalyzing(false);
        }
    };

    const fetchHealthRecords = async () => {
        if (!user) return;
        setHealthLoading(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/get_health_records.php?user_id=${user.id}`);
            const data = await response.json();
            if (data.success && data.data) {
                // The API returns pet-grouped records. Let's flatten for easy list display or adjust UI
                const allRecords = [];
                data.data.forEach(petGroup => {
                    petGroup.records.forEach(rec => {
                        allRecords.push({ ...rec, pet_name: petGroup.pet_name });
                    });
                });
                setHealthRecords(allRecords);
            }
        } catch (error) {
            console.error("Health Records Fetch Error", error);
        } finally {
            setHealthLoading(false);
        }
    };

    const handleAddHealthRecord = async () => {
        if (!user || !newHealthData.pet_id || !newHealthData.title) {
            Alert.alert("Error", "Please select a pet and enter a title.");
            return;
        }
        setHealthSaving(true);
        try {
            const response = await fetchWithTimeout(`${API_URL}/add_health_record.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...newHealthData, user_id: user.id })
            });
            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Health record added successfully!");
                setIsAddHealthModalVisible(false);
                setNewHealthData({ pet_id: '', record_type: 'Vaccination', date: new Date().toISOString().split('T')[0], title: '', description: '' });
                fetchHealthRecords();
            } else {
                Alert.alert("Error", data.error || "Failed to add record.");
            }
        } catch (error) {
            console.error("Add Health Record Error:", error);
            Alert.alert("Error", "Could not connect to the server.");
        } finally {
            setHealthSaving(false);
        }
    };

    useEffect(() => {
        if (checkoutStep === 2 && isCheckoutVisible) {
            setPaymentSubStep('Splash');
            const timer = setTimeout(() => {
                setPaymentSubStep('Contact');
            }, 1800);
            return () => clearTimeout(timer);
        }
    }, [checkoutStep, isCheckoutVisible]);

    useEffect(() => {
        if (activeItem === 'Profile' && user) {
            fetchProfile();
        }
    }, [activeItem]);

    useEffect(() => {
        if (rehomingView === 'form' && user && !rehomingData.location) {
            setRehomingData(prev => ({
                ...prev,
                location: profileData.location || user.location || '',
                city: profileData.location?.split(',')[0] || user.location?.split(',')[0] || ''
            }));
        }
    }, [rehomingView, user]);

    const [rehomingData, setRehomingData] = useState({
        pet_name: '',
        pet_type_id: '1', // Default to Dog
        gender: 'Unknown',
        reason_for_rehoming: '',
        location: '',
        city: '',
        state: ''
    });
    const [rehomingSubmitting, setRehomingSubmitting] = useState(false);

    const handleRehomingSubmit = async () => {
        if (!rehomingData.pet_name || !rehomingData.reason_for_rehoming || !rehomingData.city) {
            Alert.alert("Missing Fields", "Please fill in all required fields (Name, Reason, City).");
            return;
        }

        setRehomingSubmitting(true);
        try {
            const formData = new URLSearchParams();
            formData.append('user_id', user.id);
            formData.append('pet_name', rehomingData.pet_name);
            formData.append('pet_type_id', rehomingData.pet_type_id);
            formData.append('gender', rehomingData.gender);
            formData.append('reason_for_rehoming', rehomingData.reason_for_rehoming);
            formData.append('location', rehomingData.location || "App");
            formData.append('city', rehomingData.city);
            formData.append('state', rehomingData.state || "State");

            const response = await fetchWithTimeout(`${API_URL}/submit_rehoming.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });

            const data = await response.json();
            if (data.success) {
                Alert.alert("Success", "Your pet rehoming listing has been submitted and is pending approval.");
                setRehomingData({ pet_name: '', pet_type_id: '1', gender: 'Unknown', reason_for_rehoming: '', location: '', city: '', state: '' });
                fetchMyRehomingListings();
                setRehomingView('list');
            } else {
                Alert.alert("Error", data.error || "Failed to submit listing.");
            }
        } catch (error) {
            console.error(error);
            Alert.alert("Error", "Could not connect to the server.");
        } finally {
            setRehomingSubmitting(false);
        }
    };

    if (screen === 'login' || screen === 'signup') {
        return (
            <View style={styles.container}>
                <View style={styles.authBox}>
                    <Image source={require('./assets/icon.png')} style={styles.logo} resizeMode="contain" />
                    <Text style={styles.title}>{isPasswordResetMode ? 'Reset Password' : (screen === 'signup' ? 'Join PetCloud' : 'Welcome Back!')}</Text>
                    <Text style={styles.subtitle}>{isPasswordResetMode ? 'Enter email for reset link' : (screen === 'signup' ? 'Create a Pet Owner account' : 'Login to PetCloud')}</Text>

                    {screen === 'signup' && !isPasswordResetMode && (
                        <TextInput
                            style={styles.input}
                            placeholder="Full Name"
                            value={fullName}
                            onChangeText={setFullName}
                        />
                    )}

                    <TextInput
                        style={styles.input}
                        placeholder="Email Address"
                        value={email}
                        onChangeText={setEmail}
                        keyboardType="email-address"
                        autoCapitalize="none"
                    />

                    {!isPasswordResetMode && (
                        <>
                            <TextInput
                                style={styles.input}
                                placeholder="Password"
                                value={password}
                                onChangeText={setPassword}
                                secureTextEntry
                            />
                            {screen === 'signup' && (
                                <TextInput
                                    style={styles.input}
                                    placeholder="Confirm Password"
                                    value={confirmPassword}
                                    onChangeText={setConfirmPassword}
                                    secureTextEntry
                                />
                            )}
                        </>
                    )}

                    <TouchableOpacity
                        style={[styles.button, isProcessing && { opacity: 0.7 }]}
                        onPress={isPasswordResetMode ? handleForgotPassword : (screen === 'signup' ? handleSignup : handleLogin)}
                        disabled={isProcessing}
                    >
                        {isProcessing ? (
                            <ActivityIndicator color="white" />
                        ) : (
                            <Text style={styles.buttonText}>
                                {isPasswordResetMode ? 'Send Reset Link' : (screen === 'signup' ? 'Create Account' : 'Sign In')}
                            </Text>
                        )}
                    </TouchableOpacity>

                    <TouchableOpacity 
                        style={{ marginTop: 20 }} 
                        onPress={() => {
                            if (isPasswordResetMode) {
                                setIsPasswordResetMode(false);
                            } else {
                                setScreen(screen === 'login' ? 'signup' : 'login');
                            }
                        }}
                    >
                        <Text style={{ color: '#3b82f6', textAlign: 'center', fontWeight: 'bold' }}>
                            {isPasswordResetMode ? 'Back to Login' : (screen === 'login' ? "Don't have an account? Sign Up" : "Already have an account? Sign In")}
                        </Text>
                    </TouchableOpacity>

                    {screen === 'login' && !isPasswordResetMode && (
                        <TouchableOpacity style={{ marginTop: 15 }} onPress={() => setIsPasswordResetMode(true)}>
                            <Text style={{ color: '#64748b', textAlign: 'center', fontSize: 13 }}>Forgot Password?</Text>
                        </TouchableOpacity>
                    )}
                </View>
                <StatusBar style="auto" />
            </View>
        );
    }

    // Loading State
    if (!dashboardData && loading) {
        return (
            <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color="#3b82f6" />
            </View>
        );
    }

    const {
        greeting, pets, feeding_schedules, appointments, reminders,
        nearbyLostPets, nearbyStrays, dailyTasks, orders, lostPetReports
    } = dashboardData || {};



    const categories = [
        { id: null, name: 'All Pets' },
        { id: 1, name: 'Dogs' },
        { id: 2, name: 'Cats' },
        { id: 3, name: 'Birds' },
        { id: 4, name: 'Rabbits' }
    ];

    const fetchAdoptionListings = async (typeId = null) => {
        setAdoptionLoading(true);
        try {
            let url = `${API_URL}/get_adoption_listings.php`;
            if (typeId) {
                url += `?pet_type_id=${typeId}`;
            }
            const response = await fetchWithTimeout(url);
            const data = await response.json();
            if (data.success) {
                setAdoptionListings(data.data || []);
            }
        } catch (error) {
            console.error("Adoption fetch error:", error);
        } finally {
            setAdoptionLoading(false);
        }
    };




    // Adoption screen is rendered inline in the main return for consistency with other screens




    const renderCartModal = () => (
        <Modal
            animationType="slide"
            transparent={true}
            visible={isCartVisible}
            onRequestClose={() => setIsCartVisible(false)}
        >
            <View style={styles.modalOverlay}>
                <View style={styles.cartModalContent}>
                    <View style={styles.modalHeader}>
                        <Text style={styles.modalTitle}>Shopping Cart</Text>
                        <TouchableOpacity onPress={() => setIsCartVisible(false)}>
                            <Ionicons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    <ScrollView style={styles.cartItemsList}>
                        {cartItems.map((item) => (
                            <View key={item.cart_id} style={styles.cartItemCard}>
                                <Image source={{ uri: getImageUrl(item.image_url) }} style={styles.cartItemImg} />
                                <View style={styles.cartItemInfo}>
                                    <Text style={styles.cartItemName}>{item.name}</Text>
                                    <View style={styles.cartItemPriceRow}>
                                        <Text style={styles.cartItemPrice}>₹{parseFloat(item.price).toLocaleString('en-IN')}</Text>
                                        <Text style={styles.cartItemQty}>Qty: {item.quantity}</Text>
                                    </View>
                                </View>
                                <TouchableOpacity onPress={() => handleRemoveFromCart(item.product_id)}>
                                    <Ionicons name="trash-outline" size={20} color="#ef4444" />
                                </TouchableOpacity>
                            </View>
                        ))}
                        {cartItems.length === 0 && (
                            <View style={styles.emptyCartCentered}>
                                <Ionicons name="cart-outline" size={60} color="#e2e8f0" />
                                <Text style={styles.emptyCartText}>Your cart is empty.</Text>
                            </View>
                        )}
                    </ScrollView>

                    <View style={styles.cartFooter}>
                        <View style={styles.cartTotalRow}>
                            <Text style={styles.cartTotalLabel}>Total Amount</Text>
                            <Text style={styles.cartTotalValue}>₹{parseFloat(cartTotal).toLocaleString('en-IN')}</Text>
                        </View>
                        <TouchableOpacity
                            style={[styles.checkoutBtn, cartItems.length === 0 && { opacity: 0.5 }]}
                            disabled={cartItems.length === 0}
                            onPress={() => { setIsCartVisible(false); setIsCheckoutVisible(true); setCheckoutStep(1); }}
                        >
                            <Text style={styles.checkoutBtnText}>Proceed to Checkout</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </View>
        </Modal>
    );

    const renderCheckoutModal = () => (
        <Modal
            animationType="slide"
            transparent={true}
            visible={isCheckoutVisible}
            onRequestClose={() => !isProcessing && setIsCheckoutVisible(false)}
        >
            <View style={styles.modalOverlay}>
                <View style={styles.checkoutModalContent}>
                    <View style={styles.modalHeader}>
                        <Text style={styles.modalTitle}>
                            {checkoutStep === 1 ? 'Shipping Details' :
                                checkoutStep === 2 ? 'Review & Pay' : 'Order Success'}
                        </Text>
                        <TouchableOpacity onPress={() => setIsCheckoutVisible(false)} disabled={isProcessing}>
                            <Ionicons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    {checkoutStep === 1 && (
                        <ScrollView style={styles.checkoutFormScroll}>
                            <Text style={styles.formLabel}>Full Name</Text>
                            <TextInput
                                style={styles.formInput}
                                value={shippingDetails.name}
                                onChangeText={(text) => setShippingDetails({ ...shippingDetails, name: text })}
                                placeholder="Enter your full name"
                            />
                            <Text style={styles.formLabel}>Delivery Address</Text>
                            <TextInput
                                style={[styles.formInput, { height: 80 }]}
                                multiline
                                value={shippingDetails.address}
                                onChangeText={(text) => setShippingDetails({ ...shippingDetails, address: text })}
                                placeholder="Street, Building, etc."
                            />
                            <View style={{ flexDirection: 'row', gap: 10 }}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.formLabel}>City</Text>
                                    <TextInput
                                        style={styles.formInput}
                                        value={shippingDetails.city}
                                        onChangeText={(text) => setShippingDetails({ ...shippingDetails, city: text })}
                                        placeholder="City"
                                    />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.formLabel}>Zip Code</Text>
                                    <TextInput
                                        style={styles.formInput}
                                        keyboardType="numeric"
                                        value={shippingDetails.zip}
                                        onChangeText={(text) => setShippingDetails({ ...shippingDetails, zip: text })}
                                        placeholder="123456"
                                    />
                                </View>
                            </View>
                            <Text style={styles.formLabel}>Phone Number</Text>
                            <TextInput
                                style={styles.formInput}
                                keyboardType="phone-pad"
                                value={shippingDetails.phone}
                                onChangeText={(text) => setShippingDetails({ ...shippingDetails, phone: text })}
                                placeholder="+91 1234567890"
                            />

                            <TouchableOpacity style={styles.nextBtn} onPress={() => setCheckoutStep(2)}>
                                <Text style={styles.nextBtnText}>Continue to Payment</Text>
                            </TouchableOpacity>
                        </ScrollView>
                    )}

                    {checkoutStep === 2 && (
                        <View style={paymentSubStep === 'Methods' ? styles.razorpayContainerFull : styles.paymentContainer}>
                            {paymentSubStep === 'Splash' && (
                                <View style={styles.razorpaySplash}>
                                    <View style={styles.blueShield}>
                                        <Ionicons name="shield-checkmark" size={100} color="white" />
                                    </View>
                                    <Text style={styles.securedByText}>Secured by <Text style={{ fontWeight: 'bold' }}>Razorpay</Text></Text>
                                </View>
                            )}

                            {paymentSubStep === 'Contact' && (
                                <View style={styles.razorpayContact}>
                                    <Text style={styles.contactTitle}>Contact details</Text>
                                    <View style={styles.contactHeaderLine} />
                                    <Text style={styles.contactSub}>Enter mobile number to continue</Text>
                                    <View style={styles.phoneInputRow}>
                                        <View style={styles.flagBox}>
                                            <Text style={{ fontSize: 16 }}>🇮🇳 +91</Text>
                                            <Ionicons name="chevron-down" size={14} color="#64748b" />
                                        </View>
                                        <TextInput
                                            style={styles.razorpayPhoneInput}
                                            keyboardType="phone-pad"
                                            value={shippingDetails.phone}
                                            onChangeText={(t) => setShippingDetails({ ...shippingDetails, phone: t })}
                                            placeholder="Mobile number"
                                        />
                                    </View>
                                    <TouchableOpacity style={styles.razorpayContinueBtn} onPress={() => setPaymentSubStep('Methods')}>
                                        <Text style={styles.razorpayContinueText}>Continue</Text>
                                    </TouchableOpacity>
                                    <Text style={styles.securedByTiny}>Secured by <Text style={{ fontWeight: 'bold' }}>Razorpay</Text></Text>
                                </View>
                            )}

                            {paymentSubStep === 'Methods' && (
                                <View style={styles.razorpayMethods}>
                                    <View style={styles.razorpaySidebar}>
                                        <View style={styles.sidebarBrand}>
                                            <Image source={{ uri: getImageUrl('images/logo.png') }} style={styles.razorpayLogo} />
                                            <Text style={styles.razorpayBrandName}>PetCloud</Text>
                                        </View>
                                        <View style={styles.priceSummaryBox}>
                                            <Text style={styles.priceSummaryLabel}>Price Summary</Text>
                                            <Text style={styles.priceSummaryValue}>₹{parseFloat(cartTotal).toLocaleString('en-IN')}</Text>
                                        </View>
                                        <TouchableOpacity style={styles.userSummaryBox} onPress={() => setPaymentSubStep('Contact')}>
                                            <Ionicons name="person-circle" size={24} color="white" style={{ opacity: 0.7 }} />
                                            <View style={{ flex: 1, marginLeft: 10 }}>
                                                <Text style={styles.userSummaryLabel}>Using as</Text>
                                                <Text style={styles.userSummaryValue}>{shippingDetails.phone || 'Enter Phone'}</Text>
                                            </View>
                                            <Ionicons name="chevron-forward" size={16} color="white" style={{ opacity: 0.7 }} />
                                        </TouchableOpacity>
                                        <View style={styles.razorpayFooter}>
                                            <Image source={{ uri: 'https://cdn.razorpay.com/static/assets/logo/secured_white.png' }} style={{ width: 100, height: 20, resizeMode: 'contain' }} />
                                        </View>
                                    </View>

                                    <View style={styles.methodsContent}>
                                        <View style={styles.methodsHeader}>
                                            <Text style={styles.methodsTitle}>Payment Options</Text>
                                        </View>

                                        <ScrollView style={styles.methodsList}>
                                            {/* Cards Section */}
                                            <View style={styles.methodGroup}>
                                                <TouchableOpacity
                                                    style={selectedMethod === 'Cards' ? styles.methodItemActive : styles.methodItem}
                                                    onPress={() => setSelectedMethod('Cards')}
                                                >
                                                    <View style={{ flex: 1 }}>
                                                        <Text style={styles.methodName}>Cards</Text>
                                                        <Text style={styles.methodSub}>Visa, Mastercard, RuPay, Maestro</Text>
                                                    </View>
                                                    <Ionicons name="card" size={20} color={selectedMethod === 'Cards' ? "#10b981" : "#e2e8f0"} />
                                                </TouchableOpacity>

                                                {selectedMethod === 'Cards' && (
                                                    <View style={styles.cardForm}>
                                                        <Text style={styles.formLabelSmall}>Add a new card</Text>
                                                        <TextInput style={styles.razorpayInput} placeholder="Card Number" keyboardType="numeric" placeholderTextColor="#cbd5e1" />
                                                        <View style={{ flexDirection: 'row', gap: 10 }}>
                                                            <TextInput style={[styles.razorpayInput, { flex: 1 }]} placeholder="MM / YY" keyboardType="numeric" placeholderTextColor="#cbd5e1" />
                                                            <TextInput style={[styles.razorpayInput, { flex: 1 }]} placeholder="CVV" secureTextEntry keyboardType="numeric" placeholderTextColor="#cbd5e1" />
                                                        </View>
                                                        <View style={styles.checkboxRow}>
                                                            <Ionicons name="checkbox" size={18} color="#10b981" />
                                                            <Text style={styles.checkboxText}>Save this card as per RBI guidelines</Text>
                                                        </View>
                                                    </View>
                                                )}
                                            </View>

                                            {/* Netbanking Section */}
                                            <View style={styles.methodGroup}>
                                                <TouchableOpacity
                                                    style={selectedMethod === 'Netbanking' ? styles.methodItemActive : styles.methodItem}
                                                    onPress={() => setSelectedMethod('Netbanking')}
                                                >
                                                    <View style={{ flex: 1 }}>
                                                        <Text style={styles.methodName}>Netbanking</Text>
                                                        <Text style={styles.methodSub}>All Indian Banks</Text>
                                                    </View>
                                                    <Ionicons name="business" size={20} color={selectedMethod === 'Netbanking' ? "#10b981" : "#e2e8f0"} />
                                                </TouchableOpacity>

                                                {selectedMethod === 'Netbanking' && (
                                                    <View style={styles.bankGrid}>
                                                        <Text style={styles.formLabelSmall}>Popular Banks</Text>
                                                        <View style={styles.bankRow}>
                                                            <TouchableOpacity
                                                                style={[styles.bankIconBtn, selectedBank === 'SBI' && styles.bankIconBtnActive]}
                                                                onPress={() => setSelectedBank('SBI')}
                                                            >
                                                                <Text style={[styles.bankShortName, selectedBank === 'SBI' && styles.bankTextActive]}>SBI</Text>
                                                                <Text style={[styles.bankFullName, selectedBank === 'SBI' && styles.bankTextActive]}>SBI</Text>
                                                            </TouchableOpacity>
                                                            <TouchableOpacity
                                                                style={[styles.bankIconBtn, selectedBank === 'HDFC' && styles.bankIconBtnActive]}
                                                                onPress={() => setSelectedBank('HDFC')}
                                                            >
                                                                <Text style={[styles.bankShortName, selectedBank === 'HDFC' && styles.bankTextActive]}>HDFC</Text>
                                                                <Text style={[styles.bankFullName, selectedBank === 'HDFC' && styles.bankTextActive]}>HDFC</Text>
                                                            </TouchableOpacity>
                                                            <TouchableOpacity
                                                                style={[styles.bankIconBtn, selectedBank === 'ICICI' && styles.bankIconBtnActive]}
                                                                onPress={() => setSelectedBank('ICICI')}
                                                            >
                                                                <Text style={[styles.bankShortName, selectedBank === 'ICICI' && styles.bankTextActive]}>ICICI</Text>
                                                                <Text style={[styles.bankFullName, selectedBank === 'ICICI' && styles.bankTextActive]}>ICICI</Text>
                                                            </TouchableOpacity>
                                                        </View>
                                                        <View style={styles.bankRow}>
                                                            <TouchableOpacity
                                                                style={[styles.bankIconBtn, selectedBank === 'AXIS' && styles.bankIconBtnActive]}
                                                                onPress={() => setSelectedBank('AXIS')}
                                                            >
                                                                <Text style={[styles.bankShortName, selectedBank === 'AXIS' && styles.bankTextActive]}>AXIS</Text>
                                                                <Text style={[styles.bankFullName, selectedBank === 'AXIS' && styles.bankTextActive]}>Axis</Text>
                                                            </TouchableOpacity>
                                                            <TouchableOpacity
                                                                style={[styles.bankIconBtn, selectedBank === 'KOTAK' && styles.bankIconBtnActive]}
                                                                onPress={() => setSelectedBank('KOTAK')}
                                                            >
                                                                <Text style={[styles.bankShortName, selectedBank === 'KOTAK' && styles.bankTextActive]}>KOTAK</Text>
                                                                <Text style={[styles.bankFullName, selectedBank === 'KOTAK' && styles.bankTextActive]}>Kotak</Text>
                                                            </TouchableOpacity>
                                                            <TouchableOpacity
                                                                style={[styles.bankIconBtn, selectedBank === 'BOI' && styles.bankIconBtnActive]}
                                                                onPress={() => setSelectedBank('BOI')}
                                                            >
                                                                <Text style={[styles.bankShortName, selectedBank === 'BOI' && styles.bankTextActive]}>BOI</Text>
                                                                <Text style={[styles.bankFullName, selectedBank === 'BOI' && styles.bankTextActive]}>BOI</Text>
                                                            </TouchableOpacity>
                                                        </View>

                                                        {selectedBank && (
                                                            <TouchableOpacity
                                                                style={styles.razorpayPayBtn}
                                                                onPress={() => setPaymentSubStep('BankProcessing')}
                                                                disabled={isProcessing}
                                                            >
                                                                {isProcessing ? (
                                                                    <ActivityIndicator color="white" />
                                                                ) : (
                                                                    <Text style={styles.razorpayPayBtnText}>Pay via {selectedBank}</Text>
                                                                )}
                                                            </TouchableOpacity>
                                                        )}

                                                        <TouchableOpacity
                                                            style={styles.otherBanksBtn}
                                                            onPress={() => setSelectedBank(null)}
                                                        >
                                                            <Text style={styles.otherBanksText}>Select another bank</Text>
                                                            <Ionicons name="chevron-down" size={14} color="#64748b" />
                                                        </TouchableOpacity>
                                                    </View>
                                                )}
                                            </View>

                                            {/* Wallet Section */}
                                            <View style={styles.methodGroup}>
                                                <TouchableOpacity
                                                    style={selectedMethod === 'Wallet' ? styles.methodItemActive : styles.methodItem}
                                                    onPress={() => setSelectedMethod('Wallet')}
                                                >
                                                    <View style={{ flex: 1 }}>
                                                        <Text style={styles.methodName}>Wallet</Text>
                                                        <Text style={styles.methodSub}>PhonePe, GPay, Amazon Pay</Text>
                                                    </View>
                                                    <Ionicons name="wallet" size={20} color={selectedMethod === 'Wallet' ? "#10b981" : "#e2e8f0"} />
                                                </TouchableOpacity>

                                                {selectedMethod === 'Wallet' && (
                                                    <View style={styles.bankGrid}>
                                                        <Text style={styles.formLabelSmall}>Popular Wallets</Text>
                                                        <View style={styles.bankRow}>
                                                            <TouchableOpacity style={styles.bankIconBtn}><Ionicons name="logo-google" size={18} color="#4285F4" /><Text style={styles.bankShortName}>GPay</Text></TouchableOpacity>
                                                            <TouchableOpacity style={styles.bankIconBtn}><Ionicons name="flash-outline" size={18} color="#6739B7" /><Text style={styles.bankShortName}>PhonePe</Text></TouchableOpacity>
                                                            <TouchableOpacity style={styles.bankIconBtn}><Ionicons name="logo-amazon" size={18} color="#FF9900" /><Text style={styles.bankShortName}>Amazon</Text></TouchableOpacity>
                                                        </View>
                                                        <TouchableOpacity style={styles.otherBanksBtn}>
                                                            <Text style={styles.otherBanksText}>Show all wallets</Text>
                                                            <Ionicons name="chevron-down" size={14} color="#64748b" />
                                                        </TouchableOpacity>
                                                    </View>
                                                )}
                                            </View>

                                            {/* Pay Later Section */}
                                            <View style={styles.methodGroup}>
                                                <TouchableOpacity
                                                    style={selectedMethod === 'PayLater' ? styles.methodItemActive : styles.methodItem}
                                                    onPress={() => setSelectedMethod('PayLater')}
                                                >
                                                    <View style={{ flex: 1 }}>
                                                        <Text style={styles.methodName}>Pay Later</Text>
                                                        <Text style={styles.methodSub}>Simpl, LazyPay, ICICI</Text>
                                                    </View>
                                                    <Ionicons name="time" size={20} color={selectedMethod === 'PayLater' ? "#10b981" : "#e2e8f0"} />
                                                </TouchableOpacity>

                                                {selectedMethod === 'PayLater' && (
                                                    <View style={styles.bankGrid}>
                                                        <Text style={styles.formLabelSmall}>Available Providers</Text>
                                                        <View style={styles.bankRow}>
                                                            <TouchableOpacity style={styles.bankIconBtn}><Text style={[styles.bankShortName, { color: '#FE5E5E' }]}>Simpl</Text></TouchableOpacity>
                                                            <TouchableOpacity style={styles.bankIconBtn}><Text style={[styles.bankShortName, { color: '#28BB86' }]}>LazyPay</Text></TouchableOpacity>
                                                            <TouchableOpacity style={styles.bankIconBtn}><Text style={styles.bankShortName}>ICICI</Text></TouchableOpacity>
                                                        </View>
                                                    </View>
                                                )}
                                            </View>
                                        </ScrollView>

                                        <TouchableOpacity
                                            style={[styles.razorpayPayBtn, isProcessing && { opacity: 0.7 }]}
                                            onPress={() => {
                                                if (selectedMethod === 'Netbanking' && selectedBank) {
                                                    setPaymentSubStep('BankProcessing');
                                                } else {
                                                    handlePlaceOrder();
                                                }
                                            }}
                                            disabled={isProcessing}
                                        >
                                            {isProcessing ? <ActivityIndicator color="white" /> : <Text style={styles.razorpayPayBtnText}>Continue</Text>}
                                        </TouchableOpacity>
                                    </View>
                                </View>
                            )}
                            {paymentSubStep === 'BankProcessing' && (
                                <View style={styles.bankProcessingScreen}>
                                    <View style={styles.bankHeaderMock}>
                                        <Ionicons name="lock-closed" size={20} color="#1e293b" />
                                        <Text style={styles.bankHeaderText}>Secure Bank Gateway</Text>
                                    </View>
                                    <View style={styles.bankContentMock}>
                                        <View style={styles.bankLogoPlaceholder}>
                                            <Text style={styles.bankLogoText}>{selectedBank}</Text>
                                        </View>
                                        <Text style={styles.bankProcessingText}>Redirecting you to {selectedBank} Netbanking...</Text>
                                        <ActivityIndicator size="large" color="#3b82f6" style={{ marginVertical: 30 }} />
                                        <TouchableOpacity
                                            style={styles.simulatePayBtn}
                                            onPress={handlePlaceOrder}
                                        >
                                            <Text style={styles.simulatePayText}>Simulate Successful Login & Pay</Text>
                                        </TouchableOpacity>
                                        <TouchableOpacity
                                            onPress={() => setPaymentSubStep('Methods')}
                                            style={{ marginTop: 20 }}
                                        >
                                            <Text style={{ color: '#ef4444', fontWeight: 'bold' }}>Cancel Payment</Text>
                                        </TouchableOpacity>
                                    </View>
                                </View>
                            )}
                        </View>
                    )}

                    {checkoutStep === 3 && (
                        <View style={styles.successContainer}>
                            <View style={styles.checkmarkCircle}>
                                <Ionicons name="checkmark" size={60} color="white" />
                            </View>
                            <Text style={styles.successTitle}>Order Placed!</Text>
                            <Text style={styles.successDesc}>Your order has been received and is being processed.</Text>
                            <TouchableOpacity
                                style={styles.doneBtn}
                                onPress={() => { setIsCheckoutVisible(false); setActiveItem('My Orders'); }}
                            >
                                <Text style={styles.doneBtnText}>View My Orders</Text>
                            </TouchableOpacity>
                        </View>
                    )}
                </View>
            </View>
        </Modal>
    );

    const toggleMenu = () => {
        setMenuVisible(!menuVisible);
    };

    const renderSidebarItem = (name, icon) => {
        const isActive = activeItem === name;
        return (
            <TouchableOpacity
                style={[styles.sidebarItem, isActive && styles.sidebarItemActive]}
                onPress={() => {
                    setActiveItem(name);
                    setMenuVisible(false); // Optional: close menu on selection
                }}
            >
                <Ionicons
                    name={icon}
                    size={20}
                    color={isActive ? "white" : "#64748b"}
                />
                <Text style={[styles.sidebarText, isActive && styles.sidebarTextActive]}>
                    {name}
                </Text>
            </TouchableOpacity>
        );
    };
    const renderSidebar = () => (
        <Modal
            animationType="fade"
            transparent={true}
            visible={menuVisible}
            onRequestClose={() => setMenuVisible(false)}
        >
            <TouchableOpacity
                style={styles.sidebarOverlay}
                activeOpacity={1}
                onPress={() => setMenuVisible(false)}
            >
                <TouchableOpacity activeOpacity={1} style={styles.sidebarContainer} onPress={() => { }}>
                    <View style={styles.sidebarHeader}>
                        <Text style={styles.sidebarTitle}>PetCloud</Text>
                        <TouchableOpacity onPress={() => setMenuVisible(false)}>
                            <Ionicons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>
                    <ScrollView style={styles.sidebarContent}>
                        {renderSidebarItem('Overview', 'grid-outline')}
                        {renderSidebarItem('Profile', 'person-outline')}
                        {renderSidebarItem('Adoption', 'heart-outline')}
                        {renderSidebarItem('Pet Rehoming', 'paw-outline')}
                        {renderSidebarItem('My Pets', 'people-outline')}
                        {renderSidebarItem('Health', 'medkit-outline')}
                        {renderSidebarItem('Smart Feeder', 'hardware-chip-outline')}
                        {renderSidebarItem('Schedule', 'calendar-outline')}
                        {renderSidebarItem('Marketplace', 'cart-outline')}
                        {renderSidebarItem('My Orders', 'receipt-outline')}
                        {renderSidebarItem('Lost Pet Reports', 'alert-circle-outline')}
                    </ScrollView>
                    <View style={styles.sidebarFooter}>
                        <TouchableOpacity style={styles.sidebarItem} onPress={handleLogout}>
                            <Ionicons name="log-out-outline" size={20} color="#ef4444" />
                            <Text style={[styles.sidebarText, { color: '#ef4444' }]}>Log Out</Text>
                        </TouchableOpacity>
                    </View>
                </TouchableOpacity>
            </TouchableOpacity>
        </Modal>
    );

    // Dashboard Screen with "Website Design"

    return (
        <View style={styles.dashboardContainer}>
            {renderSidebar()}

            {/* Header - mimics top-header */}
            <View style={styles.header}>
                <TouchableOpacity style={styles.menuBtn} onPress={toggleMenu}>
                    <Ionicons name="menu" size={28} color="#64748b" />
                </TouchableOpacity>

                <View style={styles.searchBar}>
                    <Ionicons name="search" size={18} color="#94a3b8" style={{ marginRight: 8 }} />
                    <Text style={{ color: '#94a3b8' }}>Search...</Text>
                </View>

                <TouchableOpacity style={styles.iconBtn} onPress={handleLogout}>
                    <Ionicons name="log-out-outline" size={24} color="#64748b" />
                </TouchableOpacity>
            </View>

            <ScrollView
                ref={mainScrollRef}
                contentContainerStyle={styles.content}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
            >
                {activeItem === 'Profile' ? (
                    <View style={{ marginTop: 20, paddingHorizontal: 15 }}>
                            <View style={{ alignItems: 'center', marginBottom: 30 }}>
                                <View style={{ 
                                    width: 120, 
                                    height: 120, 
                                    borderRadius: 60, 
                                    backgroundColor: '#3b82f6', 
                                    alignItems: 'center', 
                                    justifyContent: 'center',
                                    shadowColor: '#000',
                                    shadowOffset: { width: 0, height: 4 },
                                    shadowOpacity: 0.1,
                                    shadowRadius: 10,
                                    elevation: 5
                                }}>
                                    {profileData.profile_image ? (
                                        <Image 
                                            source={{ uri: profileData.profile_image }} 
                                            style={{ width: 120, height: 120, borderRadius: 60 }} 
                                        />
                                    ) : (
                                        <Text style={{ fontSize: 40, color: 'white', fontWeight: 'bold' }}>
                                            {profileData.full_name ? profileData.full_name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : 'JJ'}
                                        </Text>
                                    )}
                                </View>
                                <Text style={{ fontSize: 32, fontWeight: 'bold', marginTop: 20, color: '#000' }}>Edit Profile</Text>
                            </View>
                                
                                <Text style={styles.formLabel}>Full Name</Text>
                                <TextInput
                                    style={styles.formInput}
                                    placeholder="Enter full name"
                                    value={profileData.full_name || (user ? user.full_name : '')}
                                    onChangeText={(text) => setProfileData({ ...profileData, full_name: text })}
                                />

                                <Text style={styles.formLabel}>Location</Text>
                                <TextInput
                                    style={styles.formInput}
                                    placeholder="City, State"
                                    value={profileData.location || (user ? user.location : '')}
                                    onChangeText={(text) => setProfileData({ ...profileData, location: text })}
                                />

                                <Text style={styles.formLabel}>Bio</Text>
                                <TextInput
                                    style={[styles.formInput, { height: 100, textAlignVertical: 'top' }]}
                                    value={profileData.bio}
                                    onChangeText={(text) => setProfileData({ ...profileData, bio: text })}
                                    placeholder="Tell us about yourself..."
                                    multiline
                                />

                                <TouchableOpacity 
                                    style={[styles.premiumBtn, profileSaving && { opacity: 0.7 }, { marginTop: 20 }]} 
                                    onPress={handleUpdateProfile}
                                    disabled={profileSaving}
                                >
                                    {profileSaving ? (
                                        <ActivityIndicator color="#fff" />
                                    ) : (
                                        <LinearGradient
                                            colors={['#3b82f6', '#2563eb']}
                                            start={{ x: 0, y: 0 }}
                                            end={{ x: 1, y: 1 }}
                                            style={styles.premiumBtnGradient}
                                        >
                                            <Ionicons name="checkmark-circle-outline" size={20} color="white" style={{ marginRight: 8 }} />
                                            <Text style={styles.premiumBtnText}>Save Changes</Text>
                                        </LinearGradient>
                                    )}
                                </TouchableOpacity>
                            </View>
                ) : activeItem === 'Adoption' ? (
                    <View style={{ marginTop: 20 }}>
                        <Text style={styles.pageTitle}>Find Your New Best Friend</Text>

                        <View style={{ flexDirection: 'row', gap: 10, marginHorizontal: 15, marginBottom: 15 }}>
                            <TouchableOpacity 
                                style={{ flex: 1, backgroundColor: adoptionView === 'listings' ? '#3b82f6' : '#f1f5f9', paddingVertical: 12, borderRadius: 12, alignItems: 'center' }}
                                onPress={() => setAdoptionView('listings')}
                            >
                                <Text style={{ color: adoptionView === 'listings' ? 'white' : '#64748b', fontWeight: 'bold' }}>Browse Pets</Text>
                            </TouchableOpacity>
                            <TouchableOpacity 
                                style={{ flex: 1, backgroundColor: adoptionView === 'my_applications' ? '#3b82f6' : '#f1f5f9', paddingVertical: 12, borderRadius: 12, alignItems: 'center' }}
                                onPress={() => { setAdoptionView('my_applications'); fetchAdoptionStatus(); }}
                            >
                                <Text style={{ color: adoptionView === 'my_applications' ? 'white' : '#64748b', fontWeight: 'bold' }}>My Applications</Text>
                            </TouchableOpacity>
                        </View>

                        {adoptionView === 'listings' ? (
                            <>
                                {/* Search Bar - Mimics web top search */}
                                <View style={styles.adoptionSearchContainer}>
                                    <Ionicons name="search" size={20} color="#94a3b8" />
                                    <TextInput
                                        style={styles.adoptionSearchInput}
                                        placeholder="Search for pets to adopt..."
                                        placeholderTextColor="#94a3b8"
                                    />
                                </View>

                                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.filterScroll}>
                                    {categories.map((cat) => (
                                        <TouchableOpacity
                                            key={cat.name}
                                            style={[styles.filterTab, selectedCategory === cat.name && styles.filterTabActive]}
                                            onPress={() => setSelectedCategory(cat.name)}
                                        >
                                            <Text style={[styles.filterText, selectedCategory === cat.name && styles.filterTextActive]}>{cat.name}</Text>
                                        </TouchableOpacity>
                                    ))}
                                </ScrollView>

                                {adoptionLoading ? (
                                    <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                                ) : (
                                    <View style={styles.listingsGrid}>
                                        {adoptionListings.map((pet) => (
                                            <View key={pet.id} style={styles.adoptionCard}>
                                                <Image source={{ uri: getImageUrl(pet.image) }} style={styles.adoptionImage} />
                                                <View style={styles.adoptionInfo}>
                                                    <View style={styles.adoptionHeaderRow}>
                                                        <Text style={styles.adoptionName}>{pet.pet_name}</Text>
                                                        <View style={[styles.typeTag, pet.pet_type?.name?.toLowerCase() === 'cat' && { backgroundColor: '#fef3c7' }]}>
                                                            <Text style={[styles.typeTagText, pet.pet_type?.name?.toLowerCase() === 'cat' && { color: '#92400e' }]}>
                                                                {(pet.pet_type?.name || 'Pet').toUpperCase()}
                                                            </Text>
                                                        </View>
                                                    </View>
                                                    <View style={styles.adoptionDetailsRow}>
                                                        <Text style={styles.adoptionDetails}>
                                                            {pet.age?.display || 'N/A'} • {pet.breed?.name || 'Unknown'}
                                                        </Text>
                                                    </View>
                                                    <TouchableOpacity 
                                                        style={styles.greenProfileBtn}
                                                        onPress={() => {
                                                            const mappedPet = {
                                                                ...pet,
                                                                pet_name: pet.pet_name,
                                                                pet_image: pet.image,
                                                                pet_type: pet.pet_type?.name,
                                                                pet_breed: pet.breed?.name,
                                                                pet_age: pet.age?.display || "N/A",
                                                                pet_description: pet.description || "No description available.",
                                                                pet_weight: pet.weight_kg ? pet.weight_kg + " kg" : "N/A",
                                                                pet_gender: pet.gender || "Unknown",
                                                                is_adoption: true,
                                                                listing_id: pet.id
                                                            };
                                                            setSelectedPet(mappedPet);
                                                            setIsPetModalVisible(true);
                                                        }}
                                                    >
                                                        <Text style={styles.greenProfileBtnText}>View Profile</Text>
                                                    </TouchableOpacity>
                                                </View>
                                            </View>
                                        ))}
                                        {adoptionListings.length === 0 && (
                                            <View style={styles.emptyStateContainer}>
                                                <Ionicons name="search-outline" size={60} color="#e2e8f0" />
                                                <Text style={styles.emptyStateText}>No pets found matching this category.</Text>
                                            </View>
                                        )}
                                    </View>
                                )}
                            </>
                        ) : (
                            <View style={{ paddingHorizontal: 15 }}>
                                {adoptionStatusLoading ? (
                                    <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                                ) : adoptionApplications.length === 0 ? (
                                    <View style={{ alignItems: 'center', padding: 40 }}>
                                        <Ionicons name="document-text-outline" size={60} color="#cbd5e1" />
                                        <Text style={{ color: '#64748b', marginTop: 10 }}>No applications submitted yet.</Text>
                                    </View>
                                ) : (
                                    adoptionApplications.map((app) => (
                                        <View key={app.id} style={{ backgroundColor: 'white', borderRadius: 16, padding: 15, marginBottom: 15, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 3, elevation: 1 }}>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <View>
                                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: '#1e293b' }}>{app.pet_name}</Text>
                                                    <Text style={{ fontSize: 13, color: '#64748b' }}>{app.pet_category} • Applied on {new Date(app.created_at).toLocaleDateString()}</Text>
                                                </View>
                                                <View style={{ backgroundColor: app.status === 'Approved' ? '#dcfce7' : app.status === 'Rejected' ? '#fee2e2' : '#fef9c3', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 8 }}>
                                                    <Text style={{ fontSize: 12, fontWeight: 'bold', color: app.status === 'Approved' ? '#166534' : app.status === 'Rejected' ? '#991b1b' : '#854d0e' }}>
                                                        {app.status ? app.status.toUpperCase() : 'PENDING'}
                                                    </Text>
                                                </View>
                                            </View>
                                        </View>
                                    ))
                                )}
                            </View>
                        )}
                    </View>
                ) : activeItem === 'My Pets' ? (
                    <View style={{ marginTop: 20, paddingHorizontal: 15 }}>
                        <Text style={styles.pageTitle}>My Pets</Text>

                        {petsLoading ? (
                            <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                        ) : (
                            <View style={styles.petGridRow}>
                                {userPets.map((pet) => (
                                    <View key={pet.id} style={styles.petGridCard}>
                                        <View style={styles.petCardContent}>
                                            <Image source={{ uri: getImageUrl(pet.pet_image) }} style={styles.petImageCircle} />
                                            <View style={styles.petInfoSection}>
                                                <Text style={[styles.petNameText, { textTransform: 'capitalize' }]}>{pet.pet_name}</Text>
                                                <Text style={styles.petBreedText}>
                                                    {pet.pet_breed} • {pet.pet_age || '3 months'}
                                                </Text>

                                                <View style={styles.petActionRow}>
                                                    <TouchableOpacity
                                                        style={styles.actionBtn}
                                                        onPress={() => {
                                                            const mappedPet = {
                                                                ...pet,
                                                                pet_name: pet.pet_name,
                                                                pet_image: pet.pet_image,
                                                                pet_type: pet.pet_gender === 'Male' ? 'Dog' : 'Cat',
                                                                pet_breed: pet.pet_breed,
                                                                pet_age: pet.pet_age || '1 Year',
                                                                pet_description: `${pet.pet_name} is a healthy ${pet.pet_breed}.`,
                                                                pet_weight: 'N/A',
                                                                pet_gender: pet.pet_gender || "Unknown"
                                                            };
                                                            setSelectedPet(mappedPet);
                                                            setIsPetModalVisible(true);
                                                        }}
                                                    >
                                                        <Text style={styles.actionBtnText}>Profile</Text>
                                                    </TouchableOpacity>

                                                    {pet.status === 'Lost' ? (
                                                        <TouchableOpacity
                                                            style={[styles.actionBtn, { borderColor: '#dcfce7', backgroundColor: '#f0fdf4' }]}
                                                            onPress={() => handleMarkFound(pet.id)}
                                                        >
                                                            <Text style={[styles.actionBtnText, { color: '#166534' }]}>Mark Found</Text>
                                                        </TouchableOpacity>
                                                    ) : (
                                                        <TouchableOpacity
                                                            style={[styles.actionBtn, styles.reportLostBtnSmall]}
                                                            onPress={() => handleReportLost(pet.id)}
                                                        >
                                                            <Text style={[styles.actionBtnText, styles.reportLostTextSmall]}>Report Lost</Text>
                                                        </TouchableOpacity>
                                                    )}

                                                    <TouchableOpacity
                                                        style={styles.actionBtn}
                                                        onPress={() => Alert.alert("Health Records", `No health records found for ${pet.pet_name}.`)}
                                                    >
                                                        <Text style={styles.actionBtnText}>Health</Text>
                                                    </TouchableOpacity>

                                                    <TouchableOpacity
                                                        style={styles.deleteBtnSmall}
                                                        onPress={() => {
                                                            Alert.alert(
                                                                "Delete Pet",
                                                                "Are you sure you want to remove this pet?",
                                                                [
                                                                    { text: "Cancel", style: "cancel" },
                                                                    { text: "Delete", style: "destructive", onPress: () => handleDeletePet(pet.id) }
                                                                ]
                                                            )
                                                        }}
                                                    >
                                                        <Ionicons name="trash-outline" size={16} color="#ef4444" />
                                                    </TouchableOpacity>
                                                </View>
                                            </View>
                                        </View>
                                    </View>
                                ))}

                                {/* Add Pet Card */}
                                <TouchableOpacity style={styles.petGridAddCard} onPress={() => setIsAddPetModalVisible(true)}>
                                    <View style={styles.addPetCircle}>
                                        <Ionicons name="add" size={24} color="white" />
                                    </View>
                                    <Text style={styles.addPetLinkText}>Add another family member</Text>
                                </TouchableOpacity>
                            </View>
                        )}
                    </View>
                ) : activeItem === 'Health' ? (
                    <View style={{ marginTop: 20 }}>
                        <Text style={styles.pageTitle}>Pet Health & Wellness</Text>

                        {/* Urgent Reminder (from Image 4) */}
                        {reminders.length > 0 && (
                            <View style={{ backgroundColor: '#fff1f2', borderLeftWidth: 4, borderLeftColor: '#ef4444', borderRadius: 16, padding: 20, marginBottom: 25, flexDirection: 'row', alignItems: 'center', gap: 15 }}>
                                <View style={{ width: 45, height: 45, borderRadius: 25, backgroundColor: '#fee2e2', alignItems: 'center', justifyContent: 'center' }}>
                                    <Ionicons name="alert-circle" size={24} color="#ef4444" />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: '#991b1b' }}>Urgent Reminder</Text>
                                    <Text style={{ fontSize: 13, color: '#b91c1c', marginTop: 2 }}>{reminders[0].title} is due soon! Check records for details.</Text>
                                </View>
                            </View>
                        )}

                        {/* Your Pet Profiles (Image 4) */}
                        <View style={{ marginBottom: 30 }}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                                <Text style={{ fontSize: 18, fontWeight: 'bold', color: '#1e293b' }}>Your Pet Profiles</Text>
                                <TouchableOpacity onPress={() => setIsAddPetModalVisible(true)}>
                                    <Text style={{ fontSize: 13, color: '#3b82f6', fontWeight: 'bold' }}>+ Add Pet</Text>
                                </TouchableOpacity>
                            </View>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 15, paddingRight: 20 }}>
                                {userPets.length === 0 ? (
                                    <TouchableOpacity 
                                        style={{ width: 160, height: 200, backgroundColor: '#f8fafc', borderRadius: 20, borderStyle: 'dashed', borderWidth: 2, borderColor: '#e2e8f0', justifyContent: 'center', alignItems: 'center' }}
                                        onPress={() => setIsAddPetModalVisible(true)}
                                    >
                                        <Ionicons name="add" size={30} color="#94a3b8" />
                                        <Text style={{ fontSize: 12, color: '#94a3b8', marginTop: 8 }}>Add New Pet</Text>
                                    </TouchableOpacity>
                                ) : (
                                    userPets.map((p) => (
                                        <View key={p.id} style={{ width: 180, backgroundColor: 'white', borderRadius: 20, padding: 15, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 5, elevation: 2 }}>
                                            <Image 
                                                source={{ uri: p.pet_image?.startsWith('http') ? p.pet_image : `${API_BASE_URL}/${p.pet_image}` }} 
                                                style={{ width: '100%', height: 110, borderRadius: 15, marginBottom: 12 }} 
                                            />
                                            <Text style={{ fontSize: 16, fontWeight: 'bold', color: '#1e293b' }}>{p.pet_name}</Text>
                                            <Text style={{ fontSize: 12, color: '#64748b', marginBottom: 12 }}>{p.pet_breed}</Text>
                                            
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: '#f1f5f9', paddingTop: 12 }}>
                                                <View>
                                                    <Text style={{ fontSize: 10, color: '#94a3b8', fontWeight: 'bold', textTransform: 'uppercase' }}>Weight</Text>
                                                    <Text style={{ fontSize: 12, color: '#1e293b', fontWeight: 'bold' }}>{p.pet_weight || 'N/A'}</Text>
                                                </View>
                                                <View style={{ alignItems: 'flex-end' }}>
                                                    <Text style={{ fontSize: 10, color: '#94a3b8', fontWeight: 'bold', textTransform: 'uppercase' }}>Next Visit</Text>
                                                    <Text style={{ fontSize: 12, color: '#3b82f6', fontWeight: 'bold' }}>{p.next_visit ? new Date(p.next_visit).toLocaleDateString() : 'None'}</Text>
                                                </View>
                                            </View>
                                        </View>
                                    ))
                                )}
                            </ScrollView>
                        </View>

                        {/* AI Symptom Checker Section */}
                        <View style={{ backgroundColor: 'white', borderRadius: 24, padding: 20, marginBottom: 25, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10, elevation: 4 }}>
                            <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 15, gap: 10 }}>
                                <View style={{ width: 45, height: 45, borderRadius: 22.5, backgroundColor: '#eff6ff', alignItems: 'center', justifyContent: 'center' }}>
                                    <Ionicons name="sparkles" size={22} color="#3b82f6" />
                                </View>
                                <View>
                                    <Text style={{ fontSize: 18, fontWeight: 'bold', color: '#1e293b' }}>AI Symptom Checker</Text>
                                    <Text style={{ fontSize: 11, color: '#3b82f6', fontWeight: '800' }}>POWERED BY PETCLOUD AI</Text>
                                </View>
                            </View>
                            
                            <View style={{ backgroundColor: '#f8fafc', borderRadius: 15, padding: 15, height: 200, marginBottom: 15 }}>
                                <ScrollView contentContainerStyle={{ flexGrow: 1, justifyContent: 'flex-end' }} showsVerticalScrollIndicator={false}>
                                    {symptomChat.length === 0 ? (
                                        <View style={{ alignItems: 'center', justifyContent: 'center', flex: 1, opacity: 0.5 }}>
                                            <Ionicons name="chatbubbles-outline" size={40} color="#94a3b8" />
                                            <Text style={{ marginTop: 10, color: '#64748b', textAlign: 'center' }}>Describe your pet's symptoms (e.g., "My dog is coughing")</Text>
                                        </View>
                                    ) : (
                                        symptomChat.map((msg, idx) => (
                                            <View key={idx} style={{ alignSelf: msg.type === 'user' ? 'flex-end' : 'flex-start', backgroundColor: msg.type === 'user' ? '#3b82f6' : '#e0e7ff', padding: 10, borderRadius: 15, borderBottomRightRadius: msg.type === 'user' ? 0 : 15, borderBottomLeftRadius: msg.type === 'bot' ? 0 : 15, maxWidth: '85%', marginBottom: 10 }}>
                                                <Text style={{ color: msg.type === 'user' ? 'white' : '#1e3a8a', fontSize: 13, lineHeight: 18 }}>{msg.text}</Text>
                                            </View>
                                        ))
                                    )}
                                    {symptomAnalyzing && (
                                        <View style={{ alignSelf: 'flex-start', backgroundColor: '#e0e7ff', padding: 10, borderRadius: 15, borderBottomLeftRadius: 0, marginBottom: 10 }}>
                                            <ActivityIndicator size="small" color="#3b82f6" />
                                        </View>
                                    )}
                                </ScrollView>
                            </View>

                            <View style={{ flexDirection: 'row', gap: 10 }}>
                                <TextInput 
                                    style={{ flex: 1, backgroundColor: '#f1f5f9', borderRadius: 12, paddingHorizontal: 15, height: 45, fontSize: 14 }}
                                    placeholder="Enter symptoms..."
                                    value={symptomInput}
                                    onChangeText={setSymptomInput}
                                    onSubmitEditing={handleSymptomCheck}
                                />
                                <TouchableOpacity 
                                    style={{ width: 45, height: 45, backgroundColor: '#3b82f6', borderRadius: 12, alignItems: 'center', justifyContent: 'center' }}
                                    onPress={handleSymptomCheck}
                                    disabled={symptomAnalyzing || !symptomInput.trim()}
                                >
                                    <Ionicons name="send" size={20} color="white" />
                                </TouchableOpacity>
                            </View>
                        </View>

                        {/* Health Records Section */}
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: '#1e293b' }}>Health Records</Text>
                            <TouchableOpacity style={{ backgroundColor: '#f1f5f9', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 8 }}>
                                <Text style={{ fontSize: 12, color: '#3b82f6', fontWeight: 'bold' }}>+ New Record</Text>
                            </TouchableOpacity>
                        </View>

                        {healthLoading ? (
                            <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 20 }} />
                        ) : healthRecords.length === 0 ? (
                            <View style={{ alignItems: 'center', padding: 30, backgroundColor: 'white', borderRadius: 20, borderWidth: 1, borderColor: '#f1f5f9' }}>
                                <Ionicons name="document-text-outline" size={50} color="#cbd5e1" style={{ marginBottom: 10 }} />
                                <Text style={{ color: '#64748b', fontSize: 14 }}>No health records found.</Text>
                            </View>
                        ) : (
                            healthRecords.map((record) => (
                                <View key={record.id} style={{ backgroundColor: 'white', borderRadius: 16, padding: 15, marginBottom: 15, flexDirection: 'row', alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 3, elevation: 1 }}>
                                    <View style={{ width: 45, height: 45, borderRadius: 12, backgroundColor: '#fef3c7', alignItems: 'center', justifyContent: 'center', marginRight: 15 }}>
                                        <Ionicons name={record.record_type === 'Vaccination' ? 'medical' : record.record_type === 'Surgery' ? 'cut' : 'document-text'} size={24} color="#d97706" />
                                    </View>
                                    <View style={{ flex: 1 }}>
                                        <Text style={{ fontSize: 15, fontWeight: 'bold', color: '#1e293b' }}>{record.title}</Text>
                                        <Text style={{ fontSize: 13, color: '#64748b', marginTop: 2 }}>{record.pet_name} • {record.record_type}</Text>
                                    </View>
                                    <View style={{ alignItems: 'flex-end' }}>
                                        <Text style={{ fontSize: 12, color: '#94a3b8', fontWeight: 'bold' }}>{new Date(record.date).toLocaleDateString()}</Text>
                                    </View>
                                </View>
                            ))
                        )}
                        
                        <View style={{ height: 40 }} />
                    </View>
                ) : activeItem === 'Smart Feeder' ? (
                    <View style={{ marginTop: 20 }}>
                        <Text style={styles.pageTitle}>Smart Feeder</Text>

                        {feederLoading ? (
                            <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                        ) : (
                            <View style={{ gap: 20 }}>
                                {/* Feeder Terminal Card */}
                                <View style={styles.minimalCard}>
                                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 15 }}>
                                        <View>
                                            <Text style={styles.feederCardTitle}>Feeder Terminal</Text>
                                            <Text style={styles.feederCardSubtitle}>Direct hardware control</Text>
                                        </View>
                                        <View style={styles.statusBadgeGreen}>
                                            <View style={styles.dotGreen} />
                                            <Text style={styles.statusTextGreen}>ONLINE</Text>
                                        </View>
                                    </View>

                                    {/* Last Fed Status Row */}
                                    <View style={styles.feederStatusRow}>
                                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 5 }}>
                                            <Ionicons name="time" size={16} color="#3b82f6" />
                                            <Text style={styles.feederStatusText}>Last fed: {feederData.last_feed?.time || '--'}</Text>
                                        </View>
                                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 5 }}>
                                            <Ionicons name="restaurant" size={16} color="#3b82f6" />
                                            <Text style={styles.feederStatusText}>Last portion: {feederData.last_feed?.portion || '--'}</Text>
                                        </View>
                                    </View>

                                    {/* Pet Selector */}
                                    <Text style={styles.formLabelFeeder}>Select Pet</Text>
                                    <View style={styles.feederSelectContainer}>
                                        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 10 }}>
                                            {userPets.map((p) => (
                                                <TouchableOpacity
                                                    key={p.id}
                                                    style={[styles.miniPetTab, selectedFeederPetId === p.id && styles.miniPetTabActive]}
                                                    onPress={() => setSelectedFeederPetId(p.id)}
                                                >
                                                    <Text style={[styles.miniPetTabText, selectedFeederPetId === p.id && styles.miniPetTabTextActive]}>{p.pet_name}</Text>
                                                </TouchableOpacity>
                                            ))}
                                        </ScrollView>
                                    </View>

                                    {/* Portion Grid */}
                                    <Text style={styles.formLabelFeeder}>Portion Size</Text>
                                    <View style={styles.portionGridFeeder}>
                                        <TouchableOpacity
                                            style={[styles.portionOptionFeeder, selectedPortion === 'Small' && styles.portionOptionFeederActive]}
                                            onPress={() => setSelectedPortion('Small')}
                                        >
                                            <Text style={[styles.portionOptionTitle, selectedPortion === 'Small' && styles.portionOptionTitleActive]}>Small</Text>
                                            <Text style={styles.portionOptionGrams}>30g</Text>
                                        </TouchableOpacity>
                                        <TouchableOpacity
                                            style={[styles.portionOptionFeeder, selectedPortion === 'Medium' && styles.portionOptionFeederActive]}
                                            onPress={() => setSelectedPortion('Medium')}
                                        >
                                            <Text style={[styles.portionOptionTitle, selectedPortion === 'Medium' && styles.portionOptionTitleActive]}>Medium</Text>
                                            <Text style={styles.portionOptionGrams}>60g</Text>
                                        </TouchableOpacity>
                                        <TouchableOpacity
                                            style={[styles.portionOptionFeeder, selectedPortion === 'Large' && styles.portionOptionFeederActive]}
                                            onPress={() => setSelectedPortion('Large')}
                                        >
                                            <Text style={[styles.portionOptionTitle, selectedPortion === 'Large' && styles.portionOptionFeederActive]}>Large</Text>
                                            <Text style={styles.portionOptionGrams}>100g</Text>
                                        </TouchableOpacity>
                                    </View>

                                    <TouchableOpacity style={styles.btnFeedNowFeeder} onPress={handleManualFeed}>
                                        <Ionicons name="flash" size={20} color="white" />
                                        <Text style={styles.btnFeedNowTextFeeder}>FEED NOW</Text>
                                    </TouchableOpacity>
                                </View>

                                {/* Simple Schedule Card */}
                                <View style={styles.minimalCard}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 15 }}>
                                        <Ionicons name="calendar-outline" size={20} color="#3b82f6" />
                                        <Text style={styles.feederCardTitle}>Simple Schedule</Text>
                                    </View>
                                    <View style={{ flexDirection: 'row', gap: 10, marginBottom: 15 }}>
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.formLabelFeeder}>Time (HH:MM)</Text>
                                            <TextInput
                                                style={styles.feederInputSmall}
                                                placeholder="08:00"
                                                value={scheduleTime}
                                                onChangeText={setScheduleTime}
                                            />
                                        </View>
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.formLabelFeeder}>Amount (g)</Text>
                                            <TextInput
                                                style={styles.feederInputSmall}
                                                placeholder="40"
                                                keyboardType="numeric"
                                                value={schedulePortion}
                                                onChangeText={setSchedulePortion}
                                            />
                                        </View>
                                    </View>
                                    <TouchableOpacity style={styles.btnSaveSchedFeeder} onPress={handleSaveSchedule}>
                                        <Text style={styles.btnSaveSchedTextFeeder}>Save Schedule</Text>
                                    </TouchableOpacity>
                                </View>

                                {/* Recent Activity List */}
                                <View style={styles.minimalCard}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 15 }}>
                                        <Ionicons name="list" size={20} color="#10b981" />
                                        <Text style={styles.feederCardTitle}>Recent Activity</Text>
                                    </View>
                                    {feederData.history && feederData.history.length > 0 ? (
                                        feederData.history.map((log, idx) => (
                                            <View key={idx} style={styles.feederHistoryItem}>
                                                <View>
                                                    <Text style={styles.feederHistoryName}>{log.pet_name}</Text>
                                                    <Text style={styles.feederHistoryTime}>{log.time_formatted}</Text>
                                                </View>
                                                <View style={{ alignItems: 'flex-end' }}>
                                                    <Text style={styles.feederHistoryPortion}>{log.quantity_grams}g</Text>
                                                    <View style={styles.successBadgeSmall}>
                                                        <Text style={styles.successBadgeTextSmall}>SUCCESS</Text>
                                                    </View>
                                                </View>
                                            </View>
                                        ))
                                    ) : (
                                        <Text style={styles.emptyTextFeeder}>No recent activity logs.</Text>
                                    )}
                                </View>
                            </View>
                        )}
                    </View>
                ) : activeItem === 'Marketplace' ? (
                    <View style={{ marginTop: 20 }}>
                        <View style={styles.marketplaceHeader}>
                            <Text style={styles.pageTitle}>Marketplace</Text>
                            <TouchableOpacity style={styles.cartBtn} onPress={() => setIsCartVisible(true)}>
                                <Ionicons name="cart" size={24} color="#1e293b" />
                                {cartItems.length > 0 && <View style={styles.cartBadge}><Text style={styles.cartBadgeText}>{cartItems.length}</Text></View>}
                            </TouchableOpacity>
                        </View>

                        {/* Search Bar - Matching Image */}
                        <View style={styles.searchContainerMarket}>
                            <Ionicons name="search" size={20} color="#94a3b8" />
                            <TextInput
                                style={styles.searchInputMarket}
                                placeholder="Search for food, toys, or services..."
                                placeholderTextColor="#94a3b8"
                                value={marketplaceSearch}
                                onChangeText={(text) => {
                                    setMarketplaceSearch(text);
                                    fetchMarketplace(marketplaceCategory, text);
                                }}
                            />
                        </View>

                        {/* Category Chips - Matching Image */}
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.categoryChipsScroll}>
                            {['All', 'Food', 'Toys', 'Health', 'Accessories'].map((cat) => (
                                <TouchableOpacity
                                    key={cat}
                                    style={[styles.categoryChip, marketplaceCategory === cat && styles.categoryChipActive]}
                                    onPress={() => {
                                        setMarketplaceCategory(cat);
                                        fetchMarketplace(cat, marketplaceSearch);
                                    }}
                                >
                                    <Text style={[styles.categoryChipText, marketplaceCategory === cat && styles.categoryChipTextActive]}>{cat}</Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>

                        {productsLoading ? (
                            <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                        ) : (
                            <View style={styles.marketplaceGrid}>
                                {products.map((item) => (
                                    <View key={item.id} style={styles.marketCard}>
                                        <Image source={{ uri: getImageUrl(item.image_url) }} style={styles.marketCardImage} />
                                        <View style={styles.marketCardInfo}>
                                            <Text style={styles.marketCardName} numberOfLines={1}>{item.name}</Text>
                                            <Text style={styles.marketCardDesc} numberOfLines={2}>{item.description}</Text>
                                            <View style={styles.marketCardFooter}>
                                                <Text style={styles.marketCardPrice}>₹{parseFloat(item.price).toLocaleString('en-IN')}</Text>
                                                <TouchableOpacity
                                                    style={styles.marketCardAddBtn}
                                                    onPress={() => handleAddToCart(item.id)}
                                                >
                                                    <Ionicons name="add" size={16} color="white" />
                                                    <Text style={styles.marketCardAddText}>Add</Text>
                                                </TouchableOpacity>
                                            </View>
                                        </View>
                                    </View>
                                ))}
                                {products.length === 0 && (
                                    <View style={styles.emptyStateContainerCentered}>
                                        <Ionicons name="search-outline" size={60} color="#e2e8f0" />
                                        <Text style={styles.emptyStateText}>No products found matching your search.</Text>
                                    </View>
                                )}
                            </View>
                        )}
                    </View>
                ) : activeItem === 'My Orders' ? (
                    <ScrollView
                        style={{ marginTop: 20 }}
                        refreshControl={<RefreshControl refreshing={ordersLoading} onRefresh={fetchMyOrders} color="#3b82f6" />}
                    >
                        <Text style={styles.pageTitle}>My Orders</Text>
                        <Text style={styles.pageSubtitle}>Manage and track your recent orders.</Text>

                        {ordersLoading && myOrders.length === 0 ? (
                            <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                        ) : (
                            <View style={{ gap: 15, paddingBottom: 100 }}>
                                {myOrders.map((order) => (
                                    <View key={order.id} style={styles.orderCard}>
                                        <View style={[styles.orderHeader, { alignItems: 'center' }]}>
                                            <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
                                                {order.product_image && (
                                                    <Image 
                                                        source={{ uri: getImageUrl(order.product_image) }} 
                                                        style={{ width: 40, height: 40, borderRadius: 6, marginRight: 12, backgroundColor: '#f1f5f9' }} 
                                                    />
                                                )}
                                                <View>
                                                    <Text style={styles.orderIdText}>Order #{order.id}</Text>
                                                    <Text style={styles.orderDateText}>{order.order_date ? new Date(order.order_date.replace(' ', 'T')).toLocaleDateString() : 'N/A'}</Text>
                                                </View>
                                            </View>
                                            <View style={[styles.statusBadgeSmall,
                                            order.status === 'Delivered' ? styles.statusActive :
                                                order.status === 'Processing' ? styles.statusPending : styles.statusAdopted]}>
                                                <Text style={[styles.statusBadgeTextSmall,
                                                order.status === 'Delivered' ? styles.statusBadgeTextSmall_Active :
                                                    order.status === 'Processing' ? styles.statusBadgeTextSmall_Pending : styles.statusBadgeTextSmall_Adopted]}>
                                                    {order.status.toUpperCase()}
                                                </Text>
                                            </View>
                                        </View>
                                        <View style={styles.orderFooter}>
                                            <Text style={styles.orderTotalLabel}>Total Amount:</Text>
                                            <Text style={styles.orderTotalValue}>₹{parseFloat(order.total_amount).toLocaleString('en-IN')}</Text>
                                        </View>
                                    </View>
                                ))}
                                {myOrders.length === 0 && (
                                    <View style={styles.emptyStateContainerCentered}>
                                        <Ionicons name="receipt-outline" size={60} color="#e2e8f0" />
                                        <Text style={styles.emptyStateText}>You haven't placed any orders yet.</Text>
                                        <TouchableOpacity style={styles.marketplaceGoBtn} onPress={() => setActiveItem('Marketplace')}>
                                            <Text style={styles.marketplaceGoText}>Explore Marketplace</Text>
                                        </TouchableOpacity>
                                    </View>
                                )}
                            </View>
                        )}
                    </ScrollView>
                ) : activeItem === 'Pet Rehoming' ? (
                    <View style={{ marginTop: 20, paddingHorizontal: 15 }}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20, flexWrap: 'wrap', gap: 10 }}>
                            <Text style={[styles.pageTitle, { fontSize: 20, marginBottom: 0 }]}>{rehomingView === 'list' ? 'My Rehoming Listings' : 'List a Pet'}</Text>
                            {rehomingView === 'list' ? (
                                <TouchableOpacity style={styles.stylishAddBtn} onPress={() => setRehomingView('form')}>
                                    <LinearGradient
                                        colors={['#10b981', '#059669']}
                                        style={styles.stylishAddBtnGradient}
                                    >
                                        <Ionicons name="add" size={20} color="white" style={{ marginRight: 4 }} />
                                        <Text style={styles.stylishAddBtnText}>Rehome a New Pet</Text>
                                    </LinearGradient>
                                </TouchableOpacity>
                            ) : (
                                <TouchableOpacity onPress={() => setRehomingView('list')} style={{ paddingVertical: 8 }}>
                                    <Text style={styles.linkText}>Back to List</Text>
                                </TouchableOpacity>
                            )}
                        </View>

                        {rehomingView === 'list' ? (
                            <View>
                                <Text style={styles.pageSubtitle}>Manage the pets you have listed for adoption.</Text>

                                {isListingsLoading ? (
                                    <ActivityIndicator size="large" color="#3b82f6" style={{ marginTop: 50 }} />
                                ) : (
                                    <View style={{ gap: 15, marginTop: 10 }}>
                                        {myRehomingListings.map((listing) => (
                                            <View key={listing.id} style={styles.rehomeEntryCard}>
                                                <Image source={{ uri: getImageUrl(listing.image_url) }} style={styles.rehomeEntryImg} />
                                                <View style={styles.rehomeEntryInfo}>
                                                    <View style={styles.rehomeEntryHeader}>
                                                        <Text style={styles.rehomeEntryName}>{listing.pet_name}</Text>
                                                        {(() => {
                                                            const s = listing.status.toLowerCase();
                                                            let bgStyle = styles.statusPending;
                                                            let textStyle = styles.statusBadgeTextSmall_Pending;

                                                            if (s.includes('active')) {
                                                                bgStyle = styles.statusActive;
                                                                textStyle = styles.statusBadgeTextSmall_Active;
                                                            } else if (s.includes('adopted')) {
                                                                bgStyle = styles.statusAdopted;
                                                                textStyle = styles.statusBadgeTextSmall_Adopted;
                                                            } else if (s.includes('rejected')) {
                                                                bgStyle = styles.statusRejected;
                                                                textStyle = styles.statusBadgeTextSmall_Rejected;
                                                            }

                                                            return (
                                                                <View style={[styles.statusBadgeSmall, bgStyle]}>
                                                                    <Text style={[styles.statusBadgeTextSmall, textStyle]}>
                                                                        {listing.status.replace('_', ' ').toUpperCase()}
                                                                    </Text>
                                                                </View>
                                                            );
                                                        })()}
                                                    </View>
                                                    <Text style={styles.rehomeEntrySub}>
                                                        {listing.pet_type} • {listing.breed} • {listing.age}
                                                    </Text>
                                                    <Text style={styles.rehomeEntryDate}>
                                                        Listed on {listing.created_at ? new Date(listing.created_at.replace(' ', 'T')).toDateString() : 'Recent'}
                                                    </Text>
                                                </View>
                                                <TouchableOpacity
                                                    style={styles.rehomeDeleteBtn}
                                                    onPress={() => Alert.alert("Delete Listing", "Are you sure you want to remove this listing?", [
                                                        { text: "Cancel", style: "cancel" },
                                                        { text: "Delete", style: "destructive", onPress: () => handleDeleteRehomingListing(listing.id) }
                                                    ])}
                                                >
                                                    <Ionicons name="trash-outline" size={18} color="#ef4444" />
                                                </TouchableOpacity>
                                            </View>
                                        ))}
                                        {myRehomingListings.length === 0 && (
                                            <View style={styles.emptyStateContainer}>
                                                <Ionicons name="paw-outline" size={60} color="#e2e8f0" />
                                                <Text style={styles.emptyStateText}>No pets listed yet.</Text>
                                            </View>
                                        )}
                                    </View>
                                )}
                            </View>
                        ) : (
                            <View style={styles.card}>
                                <Text style={styles.sectionTitleSmall}>Pet Details</Text>

                                <TextInput
                                    style={styles.formInput}
                                    placeholder="Pet Name *"
                                    value={rehomingData.pet_name}
                                    onChangeText={(text) => setRehomingData({ ...rehomingData, pet_name: text })}
                                />

                                <View style={{ flexDirection: 'row', gap: 10 }}>
                                    <TouchableOpacity
                                        style={[styles.formSelectBtn, rehomingData.pet_type_id === '1' && styles.formSelectBtnActive]}
                                        onPress={() => setRehomingData({ ...rehomingData, pet_type_id: '1' })}>
                                        <Text style={[styles.formSelectText, rehomingData.pet_type_id === '1' && styles.formSelectTextActive]}>Dog</Text>
                                    </TouchableOpacity>
                                    <TouchableOpacity
                                        style={[styles.formSelectBtn, rehomingData.pet_type_id === '2' && styles.formSelectBtnActive]}
                                        onPress={() => setRehomingData({ ...rehomingData, pet_type_id: '2' })}>
                                        <Text style={[styles.formSelectText, rehomingData.pet_type_id === '2' && styles.formSelectTextActive]}>Cat</Text>
                                    </TouchableOpacity>
                                </View>

                                <TextInput
                                    style={[styles.formInput, { height: 100, textAlignVertical: 'top' }]}
                                    placeholder="Reason for Rehoming *"
                                    multiline
                                    numberOfLines={4}
                                    value={rehomingData.reason_for_rehoming}
                                    onChangeText={(text) => setRehomingData({ ...rehomingData, reason_for_rehoming: text })}
                                />

                                <TextInput
                                    style={styles.formInput}
                                    placeholder="City *"
                                    value={rehomingData.city}
                                    onChangeText={(text) => setRehomingData({ ...rehomingData, city: text })}
                                />

                                <TouchableOpacity
                                    style={[styles.button, rehomingSubmitting && { opacity: 0.7 }]}
                                    onPress={handleRehomingSubmit}
                                    disabled={rehomingSubmitting}
                                >
                                    {rehomingSubmitting ? (
                                        <ActivityIndicator color="white" />
                                    ) : (
                                        <Text style={styles.buttonText}>Submit Listing</Text>
                                    )}
                                </TouchableOpacity>
                            </View>
                        )}
                    </View>

                ) : activeItem === 'Lost Pet Reports' ? (
                    <View style={{ marginTop: 20 }}>
                        <Text style={styles.pageTitle}>Lost Pet Alerts</Text>

                        {lostPetReports && lostPetReports.length > 0 ? (
                            <View style={{ gap: 15 }}>
                                <Text style={styles.pageSubtitle}>Sightings of your missing pets reported by the community.</Text>
                                {lostPetReports.map((report) => (
                                    <View key={report.id} style={styles.reportCard}>
                                        <View style={styles.reportHeader}>
                                            <Image source={{ uri: getImageUrl(report.pet_image) }} style={styles.reportPetImg} />
                                            <View style={{ flex: 1 }}>
                                                <Text style={styles.reportTitle}>Sighting of {report.pet_name}!</Text>
                                                <Text style={styles.reportDate}>{new Date(report.created_at.replace(' ', 'T')).toLocaleDateString()} • {new Date(report.created_at.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</Text>
                                            </View>
                                        </View>
                                        <View style={styles.reporterBadge}>
                                            <Ionicons name="person-circle" size={16} color="#3b82f6" />
                                            <Text style={styles.reporterText}>Reported by {report.reporter_name}</Text>
                                        </View>
                                        <View style={styles.locationRow}>
                                            <Ionicons name="location" size={16} color="#ef4444" />
                                            <Text style={styles.locationText}>{report.found_location}</Text>
                                        </View>
                                        {report.notes && (
                                            <View style={styles.notesBox}>
                                                <Text style={styles.notesText}>"{report.notes}"</Text>
                                            </View>
                                        )}
                                        <TouchableOpacity
                                            style={styles.safeBtn}
                                            onPress={() => handleMarkFound(report.pet_id)}
                                        >
                                            <Ionicons name="checkmark-done" size={18} color="white" />
                                            <Text style={styles.safeBtnText}>Mark as Safely Found</Text>
                                        </TouchableOpacity>
                                    </View>
                                ))}

                                <View style={{ height: 40 }} />
                                <Text style={styles.sectionTitleSmall}>Nearby Active Alerts</Text>
                                <Text style={styles.pageSubtitle}>Keep an eye out for these pets in your area.</Text>
                                {nearbyLostPets && nearbyLostPets.map((p) => (
                                    <View key={p.id} style={styles.lostAlertCardSmall}>
                                        <Image source={{ uri: getImageUrl(p.pet_image) }} style={styles.lostAlertImgSmall} />
                                        <View style={{ flex: 1, padding: 12 }}>
                                            <Text style={styles.lostAlertNameSmall}>{p.pet_name}</Text>
                                            <Text style={styles.lostAlertLocSmall}><Ionicons name="location" size={12} /> {p.last_seen_location}</Text>
                                            <View style={styles.lostStatusSmall}><Text style={styles.lostStatusTextSmall}>MISSING</Text></View>
                                        </View>
                                    </View>
                                ))}
                            </View>
                        ) : (
                            <View style={styles.emptyContainerFull}>
                                <View style={styles.mailboxIconContainer}>
                                    <Ionicons name="mail-open-outline" size={80} color="#e2e8f0" />
                                </View>
                                <Text style={styles.emptyTitleLarge}>No reports received yet</Text>
                                <Text style={styles.emptyDescLarge}>
                                    This page is where sightings of *your* lost pets appear. People can only report sightings if you've broadcasted an alert for a missing pet.
                                </Text>
                                <View style={styles.emptyActionRow}>
                                    <TouchableOpacity
                                        style={styles.emptyBtnSecondary}
                                        onPress={() => setActiveItem('My Pets')}
                                    >
                                        <Text style={styles.emptyBtnTextSecondary}>Go to My Pets</Text>
                                    </TouchableOpacity>
                                    <TouchableOpacity
                                        style={styles.emptyBtnPrimary}
                                        onPress={() => setActiveItem('Overview')}
                                    >
                                        <Text style={styles.emptyBtnTextPrimary}>Back to Dashboard</Text>
                                    </TouchableOpacity>
                                </View>

                                {/* Still show nearby alerts below if no personal reports */}
                                <View style={{ marginTop: 40, width: '100%' }}>
                                    <Text style={styles.sectionTitleSmall}>Active Nearby Alerts</Text>
                                    {nearbyLostPets && nearbyLostPets.length > 0 ? nearbyLostPets.map((p) => (
                                        <View key={p.id} style={styles.lostAlertCardSmall}>
                                            <Image source={{ uri: getImageUrl(p.pet_image) }} style={styles.lostAlertImgSmall} />
                                            <View style={{ flex: 1, padding: 12 }}>
                                                <Text style={styles.lostAlertNameSmall}>{p.pet_name}</Text>
                                                <Text style={styles.lostAlertLocSmall}><Ionicons name="location" size={12} /> {p.last_seen_location}</Text>
                                                <View style={styles.lostStatusSmall}><Text style={styles.lostStatusTextSmall}>MISSING</Text></View>
                                            </View>
                                        </View>
                                    )) : (
                                        <Text style={styles.emptyStateText}>No active alerts nearby.</Text>
                                    )}
                                </View>
                            </View>
                        )}
                    </View>
                ) : activeItem === 'Schedule' ? (
                    <ScrollView style={{ marginTop: 20 }} showsVerticalScrollIndicator={false}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                            <Text style={[styles.pageTitle, { fontSize: 22, color: '#1e293b' }]}>My Schedule</Text>
                            <TouchableOpacity style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#f8fafc', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20 }}>
                                <Ionicons name="add" size={16} color="#3b82f6" />
                                <Text style={{ fontSize: 13, color: '#3b82f6', fontWeight: 'bold', marginLeft: 4 }}>New Appointment</Text>
                            </TouchableOpacity>
                        </View>

                        {/* Dummy Past Appointments */}
                        <View style={{ gap: 15, marginBottom: 40 }}>
                            <View style={{ backgroundColor: 'white', borderRadius: 12, borderWidth: 1, borderColor: '#f1f5f9', padding: 15, flexDirection: 'row', alignItems: 'center' }}>
                                <View style={{ alignItems: 'center', paddingRight: 15, borderRightWidth: 1, borderRightColor: '#f1f5f9' }}>
                                    <Text style={{ fontSize: 16, fontWeight: '700', color: '#1e293b' }}>11</Text>
                                    <Text style={{ fontSize: 11, fontWeight: '600', color: '#64748b' }}>MAR</Text>
                                </View>
                                <View style={{ flex: 1, paddingLeft: 15 }}>
                                    <View style={[styles.statusBadgeSmall, { backgroundColor: '#f0fdf4', alignSelf: 'flex-start', marginBottom: 4 }]}>
                                        <Text style={[styles.statusBadgeTextSmall, { color: '#166534', fontSize: 9 }]}>COMPLETED</Text>
                                    </View>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                                        <Text style={{ fontSize: 13, color: '#64748b' }}><Ionicons name="time-outline" size={13} /> 10:30 AM</Text>
                                        <Text style={{ fontSize: 13, color: '#64748b' }}><Ionicons name="location-outline" size={13} /> PetCloud Partner</Text>
                                    </View>
                                </View>
                            </View>

                            <View style={{ backgroundColor: 'white', borderRadius: 12, borderWidth: 1, borderColor: '#f1f5f9', padding: 15, flexDirection: 'row', alignItems: 'center' }}>
                                <View style={{ alignItems: 'center', paddingRight: 15, borderRightWidth: 1, borderRightColor: '#f1f5f9' }}>
                                    <Text style={{ fontSize: 16, fontWeight: '700', color: '#1e293b' }}>15</Text>
                                    <Text style={{ fontSize: 11, fontWeight: '600', color: '#64748b' }}>MAR</Text>
                                </View>
                                <View style={{ flex: 1, paddingLeft: 15 }}>
                                    <Text style={{ fontSize: 15, fontWeight: 'bold', color: '#1e293b', marginBottom: 2 }}>General for Pet</Text>
                                    <View style={[styles.statusBadgeSmall, { backgroundColor: '#f0fdf4', alignSelf: 'flex-start', marginBottom: 4 }]}>
                                        <Text style={[styles.statusBadgeTextSmall, { color: '#166534', fontSize: 9 }]}>COMPLETED</Text>
                                    </View>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                                        <Text style={{ fontSize: 13, color: '#64748b' }}><Ionicons name="time-outline" size={13} /> 10:00 AM</Text>
                                        <Text style={{ fontSize: 13, color: '#64748b' }}><Ionicons name="location-outline" size={13} /> City Pet Clinic</Text>
                                    </View>
                                </View>
                            </View>

                            <View style={{ backgroundColor: 'white', borderRadius: 12, borderWidth: 1, borderColor: '#f1f5f9', padding: 15, flexDirection: 'row', alignItems: 'center' }}>
                                <View style={{ alignItems: 'center', paddingRight: 15, borderRightWidth: 1, borderRightColor: '#f1f5f9' }}>
                                    <Text style={{ fontSize: 16, fontWeight: '700', color: '#10b981' }}>18</Text>
                                    <Text style={{ fontSize: 11, fontWeight: '600', color: '#10b981' }}>MAR</Text>
                                </View>
                                <View style={{ flex: 1, paddingLeft: 15 }}>
                                    <Text style={{ fontSize: 15, fontWeight: 'bold', color: '#1e293b', marginBottom: 4 }}>Emergency Consultation for enzo</Text>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                                        <Text style={{ fontSize: 13, color: '#64748b' }}><Ionicons name="time-outline" size={13} /> 9:00 AM</Text>
                                        <Text style={{ fontSize: 13, color: '#64748b' }}><Ionicons name="location-outline" size={13} /> City Pet Clinic</Text>
                                    </View>
                                </View>
                                <TouchableOpacity style={{ borderWidth: 1, borderColor: '#fee2e2', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 6 }}>
                                    <Text style={{ color: '#ef4444', fontSize: 10, fontWeight: 'bold' }}>CANCEL</Text>
                                </TouchableOpacity>
                            </View>
                        </View>

                        {/* Schedule New Appointment Form */}
                        <View style={{ backgroundColor: 'white', borderRadius: 16, padding: 20, borderWidth: 1, borderColor: '#e2e8f0', marginBottom: 40, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 3 }}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 25 }}>
                                <View>
                                    <Text style={{ fontSize: 18, fontWeight: 'bold', color: '#1e293b', marginBottom: 4 }}>Schedule New Appointment</Text>
                                    <Text style={{ fontSize: 13, color: '#64748b' }}>Find the best care for your furry friend</Text>
                                </View>
                                <TouchableOpacity style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                                    <View style={{ width: 6, height: 6, borderRadius: 3, backgroundColor: '#10b981' }} />
                                    <Text style={{ fontSize: 12, color: '#64748b', fontWeight: '500' }}>Step 1: Details</Text>
                                </TouchableOpacity>
                            </View>

                            <Text style={{ fontSize: 11, fontWeight: 'bold', color: '#94a3b8', letterSpacing: 1, marginBottom: 15 }}><Ionicons name="paw" size={12} /> PET DETAILS</Text>
                            
                            <View style={{ flexDirection: 'row', gap: 15, marginBottom: 25 }}>
                                <View style={{ flex: 2 }}>
                                    <Text style={{ fontSize: 12, color: '#64748b', marginBottom: 8 }}>Pet Name</Text>
                                    <TextInput 
                                        style={{ borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 8, paddingHorizontal: 15, height: 45, backgroundColor: '#f8fafc', fontSize: 14 }}
                                        placeholder="e.g. Bella"
                                        placeholderTextColor="#cbd5e1"
                                    />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={{ fontSize: 12, color: '#64748b', marginBottom: 8 }}>Breed</Text>
                                    <View style={{ borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 8, paddingHorizontal: 15, height: 45, backgroundColor: '#f8fafc', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
                                        <Text style={{ fontSize: 14, color: '#1e293b' }}>Dog</Text>
                                        <Ionicons name="chevron-down" size={16} color="#94a3b8" />
                                    </View>
                                </View>
                            </View>

                            <Text style={{ fontSize: 11, fontWeight: 'bold', color: '#94a3b8', letterSpacing: 1, marginBottom: 15 }}><Ionicons name="grid" size={12} /> SELECT CATEGORY</Text>
                            
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 30 }}>
                                <View style={{ flexDirection: 'row', gap: 12 }}>
                                    {scheduleCategories.map((cat) => (
                                        <TouchableOpacity 
                                            key={cat.id}
                                            style={{ 
                                                width: 100, 
                                                height: 90, 
                                                borderRadius: 12, 
                                                borderWidth: 1, 
                                                borderColor: scheduleData.service_id === cat.id ? '#3b82f6' : '#e2e8f0', 
                                                backgroundColor: scheduleData.service_id === cat.id ? '#eff6ff' : 'white',
                                                alignItems: 'center', 
                                                justifyContent: 'center',
                                                padding: 10
                                            }}
                                            onPress={() => {
                                                setScheduleData({ ...scheduleData, service_id: cat.id });
                                                setEstimation(cat.price);
                                            }}
                                        >
                                            <Ionicons name={cat.icon} size={24} color={scheduleData.service_id === cat.id ? '#3b82f6' : '#64748b'} style={{ marginBottom: 8 }} />
                                            <Text style={{ fontSize: 11, fontWeight: '600', color: scheduleData.service_id === cat.id ? '#1e293b' : '#64748b', textAlign: 'center' }} numberOfLines={2}>
                                                {cat.title}
                                            </Text>
                                        </TouchableOpacity>
                                    ))}
                                </View>
                            </ScrollView>

                            <View style={{ borderTopWidth: 1, borderTopColor: '#f1f5f9', borderStyle: 'dashed', paddingTop: 20, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                <View>
                                    <Text style={{ fontSize: 10, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: 0.5 }}>Total estimation</Text>
                                    <Text style={{ fontSize: 20, fontWeight: '800', color: '#1e293b' }}>₹{estimation}</Text>
                                </View>
                                <TouchableOpacity 
                                    style={{ backgroundColor: '#0f172a', paddingHorizontal: 20, paddingVertical: 12, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 8 }}
                                    onPress={() => setPaymentModalVisible(true)}
                                >
                                    <Text style={{ color: 'white', fontSize: 14, fontWeight: '600' }}>Secure Payment & Book</Text>
                                    <Ionicons name="lock-closed" size={14} color="white" />
                                </TouchableOpacity>
                            </View>
                        </View>
                    </ScrollView>

                ) : (
                    <>
                        {/* Lost Pet Alert Banner */}
                        {/* Lost Pet Alert Banner - Matching Premium Image */}
                        {nearbyLostPets && nearbyLostPets.length > 0 && (
                            <TouchableOpacity style={styles.premiumBannerLost} onPress={() => setActiveItem('Lost Pet Reports')}>
                                <LinearGradient
                                    colors={['#ef4444', '#dc2626']}
                                    start={{ x: 0, y: 0 }}
                                    end={{ x: 1, y: 0 }}
                                    style={styles.bannerGradient}
                                >
                                    <View style={styles.bannerLeftScale}>
                                        <View style={styles.bannerIconWhite}>
                                            <Ionicons name="megaphone" size={24} color="#ef4444" />
                                        </View>
                                        <View>
                                            <Text style={styles.bannerTitleWhite}>LOST PET ALERT!</Text>
                                            <Text style={styles.bannerSubtitleWhite}>{nearbyLostPets[0].pet_name} missing nearby. Tap to help find.</Text>
                                        </View>
                                    </View>
                                    <Ionicons name="chevron-forward" size={20} color="white" />
                                </LinearGradient>
                            </TouchableOpacity>
                        )}

                        {/* Hero Section */}
                        <View style={styles.heroSection}>
                            <Image source={require('./assets/dashboard_hero_v3.png')} style={styles.heroBackground} />
                            <View style={styles.heroOverlay}>
                                <Text style={styles.greetingText}>{greeting}, {user?.full_name?.split(' ')[0]}!</Text>
                                <Text style={styles.heroSubText}>"The world would be a nicer place if everyone had the ability to love as unconditionally as a dog."</Text>
                            </View>
                        </View>

                        {/* My Family (Pets) */}
                        <View style={styles.sectionHeader}>
                            <Text style={styles.sectionTitle}>My Family</Text>
                            <TouchableOpacity onPress={() => setActiveItem('My Pets')}><Text style={styles.linkText}>View All</Text></TouchableOpacity>
                        </View>

                        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.petsScroll}>
                            {pets && pets.map((pet) => (
                                <TouchableOpacity key={pet.id} style={styles.petCard} onPress={() => setActiveItem('My Pets')}>
                                    <Image source={{ uri: getImageUrl(pet.pet_image) }} style={styles.petImage} />
                                    <Text style={styles.petName}>{pet.pet_name}</Text>
                                    <Text style={styles.petBreed}>{pet.pet_breed}</Text>
                                    {pet.status === 'Lost' && <Text style={styles.lostBadge}>LOST</Text>}
                                </TouchableOpacity>
                            ))}
                            <TouchableOpacity style={styles.addPetCard} onPress={() => setActiveItem('My Pets')}>
                                <Ionicons name="add" size={32} color="#94a3b8" />
                                <Text style={{ fontSize: 12, color: '#94a3b8', fontWeight: 'bold' }}>Add Pet</Text>
                            </TouchableOpacity>
                        </ScrollView>
                        <View style={styles.sectionHeader}>
                            <Text style={styles.sectionTitle}>Adopt Me Today</Text>
                            <TouchableOpacity onPress={() => setActiveItem('Adoption')}><Text style={styles.linkText}>See All</Text></TouchableOpacity>
                        </View>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.petsScroll}>
                            {adoptionListings && adoptionListings.slice(0, 5).map((pet) => (
                                <TouchableOpacity
                                    key={pet.id}
                                    style={styles.petCard}
                                    onPress={() => {
                                        const mappedPet = {
                                            ...pet,
                                            pet_name: pet.pet_name,
                                            pet_image: pet.image,
                                            pet_type: pet.pet_type?.name,
                                            pet_breed: pet.breed?.name,
                                            pet_age: (pet.age?.years || 0) + " yrs",
                                            pet_description: pet.description || "No description available.",
                                            pet_weight: (pet.weight_kg ? pet.weight_kg + " kg" : "N/A"),
                                            pet_gender: pet.gender || "Unknown"
                                        };
                                        setSelectedPet(mappedPet);
                                        setIsPetModalVisible(true);
                                    }}
                                >
                                    <Image source={{ uri: getImageUrl(pet.image) }} style={styles.petImage} />
                                    <Text style={styles.petName}>{pet.pet_name}</Text>
                                    <Text style={styles.petBreed}>{pet.pet_type?.name || 'Pet'}</Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>

                        <View style={styles.gridContainer}>
                            <TouchableOpacity style={styles.card} onPress={() => setActiveItem('Schedule')}>
                                <View style={styles.cardHeader}>
                                    <View style={styles.iconTitle}>
                                        <View style={[styles.iconBox, { backgroundColor: '#f3e8ff' }]}><Ionicons name="medkit" size={20} color="#9333ea" /></View>
                                        <Text style={styles.cardTitle}>Visits</Text>
                                    </View>
                                </View>
                                {appointments && appointments.length > 0 ? (
                                    appointments.slice(0, 1).map((appt, i) => (
                                        <View key={i} style={styles.apptItem}>
                                            <Text style={styles.apptTitle}>{appt.service_type}</Text>
                                            <Text style={styles.apptPet}>{appt.appointment_date}</Text>
                                        </View>
                                    ))
                                ) : <Text style={styles.emptyText}>No visits.</Text>}
                            </TouchableOpacity>
                        </View>
                    </>
                )
                }
                <View style={{ height: 40 }} />
            </ScrollView>
            {/* Pet Profile Modal */}
            <Modal
                animationType="slide"
                transparent={true}
                visible={isPetModalVisible}
                onRequestClose={() => setIsPetModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.profileModalContent}>
                        <TouchableOpacity style={styles.closeBtn} onPress={() => setIsPetModalVisible(false)}>
                            <Ionicons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>

                        {selectedPet && (
                            <ScrollView showsVerticalScrollIndicator={false}>
                                <Image source={{ uri: getImageUrl(selectedPet.pet_image) }} style={styles.modalPetImg} />
                                <View style={styles.modalInfoPadding}>
                                    <View style={styles.badgeRow}>
                                        <Text style={styles.petTypeBadge}>{selectedPet.pet_type || 'Pet'}</Text>
                                        {selectedPet.status === 'Lost' && <Text style={styles.lostBadge}>LOST</Text>}
                                    </View>

                                    <Text style={styles.modalPetName}>{selectedPet.pet_name}</Text>
                                    <Text style={styles.modalPetSub}>{selectedPet.pet_breed} • {selectedPet.pet_age || '1 Year'}</Text>

                                    <View style={styles.aboutSection}>
                                        <Text style={styles.sectionTitleSmall}>About this pet</Text>
                                        <Text style={styles.aboutText}>{selectedPet.pet_description || 'No description available for this wonderful pet yet.'}</Text>
                                    </View>

                                    <View style={styles.statsRow}>
                                        <View style={styles.statBox}>
                                            <Text style={styles.statLabel}>WEIGHT</Text>
                                            <Text style={styles.statValue}>{selectedPet.pet_weight || 'Unknown'}</Text>
                                        </View>
                                        <View style={styles.statBox}>
                                            <Text style={styles.statLabel}>GENDER</Text>
                                            <Text style={styles.statValue}>{selectedPet.pet_gender || 'Unknown'}</Text>
                                        </View>
                                    </View>

                                    <View style={styles.modalActionGrid}>
                                        {selectedPet.is_adoption ? (
                                            <TouchableOpacity style={[styles.modalActionBtn, { backgroundColor: '#10b981', width: '100%' }]}
                                                onPress={() => { setIsPetModalVisible(false); setIsAdoptionFormVisible(true); }}>
                                                <Text style={styles.modalActionTextWhite}>Apply for Adoption</Text>
                                            </TouchableOpacity>
                                        ) : (
                                            <TouchableOpacity style={[styles.modalActionBtn, { backgroundColor: '#3b82f6', width: '100%' }]}
                                                onPress={() => { setIsPetModalVisible(false); setActiveItem('Schedule'); }}>
                                                <Text style={styles.modalActionTextWhite}>Schedule Vet</Text>
                                            </TouchableOpacity>
                                        )}
                                    </View>
                                </View>
                            </ScrollView>
                        )}
                    </View>
                </View>
            </Modal>

            {/* Add Health Record Modal */}
            <Modal
                animationType="slide"
                transparent={true}
                visible={isAddHealthModalVisible}
                onRequestClose={() => setIsAddHealthModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.dashboardModalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>New Health Record</Text>
                            <TouchableOpacity onPress={() => setIsAddHealthModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#64748b" />
                            </TouchableOpacity>
                        </View>

                        <ScrollView style={{ padding: 20 }}>
                            <Text style={styles.formLabel}>Select Pet</Text>
                            <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 15 }}>
                                {userPets.map(p => (
                                    <TouchableOpacity 
                                        key={p.id}
                                        style={[{ paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, borderWidth: 1, borderColor: '#e2e8f0' }, newHealthData.pet_id === p.id && { backgroundColor: '#3b82f6', borderColor: '#3b82f6' }]}
                                        onPress={() => setNewHealthData({...newHealthData, pet_id: p.id})}
                                    >
                                        <Text style={[{ color: '#64748b', fontSize: 13 }, newHealthData.pet_id === p.id && { color: 'white', fontWeight: 'bold' }]}>{p.pet_name}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            <Text style={styles.formLabel}>Record Type</Text>
                            <View style={{ flexDirection: 'row', gap: 10, marginBottom: 15 }}>
                                {['Vaccination', 'Checkup', 'Surgery', 'Deworming'].map(type => (
                                    <TouchableOpacity 
                                        key={type}
                                        style={[{ flex: 1, paddingVertical: 10, borderRadius: 10, borderWidth: 1, borderColor: '#e2e8f0', alignItems: 'center' }, newHealthData.record_type === type && { backgroundColor: '#eff6ff', borderColor: '#3b82f6' }]}
                                        onPress={() => setNewHealthData({...newHealthData, record_type: type})}
                                    >
                                        <Text style={[{ color: '#64748b', fontSize: 11 }, newHealthData.record_type === type && { color: '#3b82f6', fontWeight: 'bold' }]}>{type}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            <Text style={styles.formLabel}>Title</Text>
                            <TextInput
                                style={styles.formInput}
                                value={newHealthData.title}
                                onChangeText={(val) => setNewHealthData({...newHealthData, title: val})}
                                placeholder="e.g. Annual Rabies Vaccination"
                            />

                            <Text style={styles.formLabel}>Date</Text>
                            <TextInput
                                style={styles.formInput}
                                value={newHealthData.date}
                                onChangeText={(val) => setNewHealthData({...newHealthData, date: val})}
                                placeholder="YYYY-MM-DD"
                            />

                            <Text style={styles.formLabel}>Description / Notes</Text>
                            <TextInput
                                style={[styles.formInput, { height: 80, textAlignVertical: 'top' }]}
                                value={newHealthData.description}
                                onChangeText={(val) => setNewHealthData({...newHealthData, description: val})}
                                placeholder="Any additional notes..."
                                multiline
                            />

                            <TouchableOpacity 
                                style={[styles.payBtn, healthSaving && { opacity: 0.7 }]}
                                onPress={handleAddHealthRecord}
                                disabled={healthSaving}
                            >
                                {healthSaving ? <ActivityIndicator color="#fff" /> : <Text style={styles.payBtnText}>Save Record</Text>}
                            </TouchableOpacity>
                            <View style={{ height: 40 }} />
                        </ScrollView>
                    </View>
                </View>
            </Modal>

            {renderCartModal()}
            {renderCheckoutModal()}

            <StatusBar style="dark" />

            {/* Payment Modal */}
            <Modal
                animationType="fade"
                transparent={true}
                visible={paymentModalVisible}
                onRequestClose={() => !paymentProcessing && setPaymentModalVisible(false)}
            >
                <View style={[styles.modalOverlay, { justifyContent: 'center', padding: 20 }]}>
                    <View style={styles.paymentCard}>
                        {paymentSuccess ? (
                            <View style={styles.paymentSuccessContent}>
                                <View style={styles.successIconCircle}>
                                    <Ionicons name="checkmark" size={60} color="#10b981" />
                                </View>
                                <Text style={styles.paymentSuccessTitle}>Payment Successful!</Text>
                                <Text style={styles.paymentSuccessSub}>Your appointment has been confirmed.</Text>
                            </View>
                        ) : (
                            <>
                                <View style={styles.paymentHeader}>
                                    <Text style={styles.paymentTitle}>Secure Checkout</Text>
                                    {!paymentProcessing && (
                                        <TouchableOpacity onPress={() => setPaymentModalVisible(false)}>
                                            <Ionicons name="close" size={24} color="#64748b" />
                                        </TouchableOpacity>
                                    )}
                                </View>

                                <View style={styles.paymentAmountBox}>
                                    <Text style={styles.amountLabel}>Total Due</Text>
                                    <Text style={styles.amountValue}>₹{selectedHospital?.price || estimation}</Text>
                                </View>

                                <View style={styles.paymentMethods}>
                                    <Text style={styles.sectionTitleSmall}>Select Payment Method</Text>
                                    <TouchableOpacity
                                        style={[styles.paymentOption, selectedMethod === 'Cards' && styles.paymentOptionActive]}
                                        onPress={() => setSelectedMethod('Cards')}
                                    >
                                        <Ionicons name="card-outline" size={20} color="#3b82f6" />
                                        <Text style={styles.paymentOptionText}>Credit / Debit Card</Text>
                                        <Ionicons name={selectedMethod === 'Cards' ? "radio-button-on" : "radio-button-off"} size={20} color="#3b82f6" />
                                    </TouchableOpacity>

                                    <TouchableOpacity
                                        style={[styles.paymentOption, selectedMethod === 'Netbanking' && styles.paymentOptionActive]}
                                        onPress={() => setSelectedMethod('Netbanking')}
                                    >
                                        <Ionicons name="business-outline" size={20} color="#3b82f6" />
                                        <Text style={styles.paymentOptionText}>Netbanking</Text>
                                        <Ionicons name={selectedMethod === 'Netbanking' ? "radio-button-on" : "radio-button-off"} size={20} color="#3b82f6" />
                                    </TouchableOpacity>

                                    {selectedMethod === 'Netbanking' && (
                                        <View style={{ paddingHorizontal: 15, paddingVertical: 10, backgroundColor: '#f8fafc', borderRadius: 10, marginTop: 5 }}>
                                            <Text style={{ fontSize: 11, color: '#64748b', marginBottom: 8, fontWeight: '700' }}>POPULAR BANKS</Text>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                                                {['SBI', 'HDFC', 'ICICI', 'AXIS'].map(bank => (
                                                    <TouchableOpacity
                                                        key={bank}
                                                        onPress={() => setSelectedBank(bank)}
                                                        style={{ alignItems: 'center', padding: 8, borderWidth: 1, borderColor: selectedBank === bank ? '#3b82f6' : '#e2e8f0', borderRadius: 8, minWidth: 60, backgroundColor: 'white' }}
                                                    >
                                                        <Text style={{ fontSize: 10, fontWeight: 'bold', color: selectedBank === bank ? '#3b82f6' : '#64748b' }}>{bank}</Text>
                                                    </TouchableOpacity>
                                                ))}
                                            </View>
                                        </View>
                                    )}

                                    <TouchableOpacity
                                        style={[styles.paymentOption, selectedMethod === 'Wallet' && styles.paymentOptionActive]}
                                        onPress={() => setSelectedMethod('Wallet')}
                                    >
                                        <Ionicons name="phone-portrait-outline" size={20} color="#3b82f6" />
                                        <Text style={styles.paymentOptionText}>UPI / Google Pay</Text>
                                        <Ionicons name={selectedMethod === 'Wallet' ? "radio-button-on" : "radio-button-off"} size={20} color="#3b82f6" />
                                    </TouchableOpacity>
                                </View>

                                <TouchableOpacity
                                    style={[styles.payNowBtn, paymentProcessing && styles.payNowBtnDisabled]}
                                    onPress={handleProcessPayment}
                                    disabled={paymentProcessing}
                                >
                                    {paymentProcessing ? (
                                        <ActivityIndicator color="white" />
                                    ) : (
                                        <Text style={styles.payNowBtnText}>Pay Now ₹{selectedHospital?.price || estimation}</Text>
                                    )}
                                </TouchableOpacity>
                                <Text style={styles.secureText}>
                                    <Ionicons name="shield-checkmark" size={12} color="#10b981" /> 256-bit Secure Encryption
                                </Text>
                            </>
                        )}
                    </View>
                </View>
            </Modal>

            {/* Adoption Application Modal */}
            <Modal animationType="slide" transparent={true} visible={isAdoptionFormVisible} onRequestClose={() => setIsAdoptionFormVisible(false)}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Apply for Adoption</Text>
                            <TouchableOpacity onPress={() => setIsAdoptionFormVisible(false)}><Ionicons name="close" size={24} color="#64748b" /></TouchableOpacity>
                        </View>
                        <ScrollView style={{ maxHeight: 500 }}>
                            <Text style={{ fontSize: 13, color: '#64748b', marginBottom: 15 }}>Applying for: <Text style={{ fontWeight: 'bold', color: '#10b981' }}>{selectedPet?.pet_name}</Text></Text>
                            <Text style={styles.formLabel}>Phone Number *</Text>
                            <TextInput 
                                style={styles.formInput} 
                                placeholder="Phone Number" 
                                keyboardType="phone-pad"
                                value={adoptionFormData.phone} 
                                onChangeText={(text) => setAdoptionFormData({ ...adoptionFormData, phone: text })} 
                            />
                            
                            <Text style={styles.formLabel}>Living Situation *</Text>
                            <View style={{ flexDirection: 'row', gap: 10, marginBottom: 15 }}>
                                {['House', 'Apartment', 'Studio'].map(l => (
                                    <TouchableOpacity 
                                        key={l} 
                                        style={[{ paddingVertical: 10, paddingHorizontal: 15, borderRadius: 10, borderWidth: 1, borderColor: '#e2e8f0', backgroundColor: '#f8fafc' }, adoptionFormData.living_situation === l && { borderColor: '#10b981', backgroundColor: '#ecfdf5' }]} 
                                        onPress={() => setAdoptionFormData({ ...adoptionFormData, living_situation: l })}
                                    >
                                        <Text style={[{ fontSize: 12, color: '#64748b', fontWeight: 'bold' }, adoptionFormData.living_situation === l && { color: '#10b981' }]}>{l}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 15, gap: 10 }}>
                                <TouchableOpacity 
                                    style={{ width: 24, height: 24, borderRadius: 6, borderWidth: 1, borderColor: '#10b981', backgroundColor: adoptionFormData.other_pets ? '#10b981' : 'transparent', alignItems: 'center', justifyContent: 'center' }}
                                    onPress={() => setAdoptionFormData({ ...adoptionFormData, other_pets: !adoptionFormData.other_pets })}
                                >
                                    {adoptionFormData.other_pets && <Ionicons name="checkmark" size={16} color="white" />}
                                </TouchableOpacity>
                                <Text style={{ fontSize: 13, color: '#1e293b', fontWeight: 'bold' }}>Do you have other pets?</Text>
                            </View>

                            <Text style={styles.formLabel}>Reason for Adoption *</Text>
                            <TextInput 
                                style={[styles.formInput, { height: 80, textAlignVertical: 'top' }]} 
                                placeholder="Why do you want this pet?" 
                                multiline
                                value={adoptionFormData.reason} 
                                onChangeText={(text) => setAdoptionFormData({ ...adoptionFormData, reason: text })} 
                            />

                            <TouchableOpacity
                                style={[styles.button, { backgroundColor: '#10b981' }, adoptionSubmitting && { opacity: 0.7 }]}
                                onPress={handleApplyAdoption}
                                disabled={adoptionSubmitting}
                            >
                                {adoptionSubmitting ? <ActivityIndicator color="white" /> : <Text style={styles.buttonText}>Submit Application</Text>}
                            </TouchableOpacity>
                        </ScrollView>
                    </View>
                </View>
            </Modal>

            {/* Add Pet Modal */}
            <Modal animationType="slide" transparent={false} visible={isAddPetModalVisible} onRequestClose={() => setIsAddPetModalVisible(false)}>
                <View style={{ flex: 1, backgroundColor: '#f8fafc' }}>
                    <View style={{ flex: 1, padding: 0, overflow: 'hidden', width: '100%' }}>
                        <LinearGradient colors={['#3b82f6', '#2563eb']} style={{ padding: 25, paddingTop: Platform.OS === 'ios' ? 60 : 40, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                            <View>
                                <Text style={{ color: 'white', fontSize: 24, fontWeight: '800' }}>Add New Pet</Text>
                                <Text style={{ color: 'rgba(255,255,255,0.8)', fontSize: 13, marginTop: 2 }}>Grow your pet family</Text>
                            </View>
                            <TouchableOpacity 
                                onPress={() => setIsAddPetModalVisible(false)}
                                style={{ backgroundColor: 'rgba(255,255,255,0.2)', padding: 8, borderRadius: 12 }}
                            >
                                <Ionicons name="close" size={20} color="white" />
                            </TouchableOpacity>
                        </LinearGradient>

                        <ScrollView style={{ padding: 25, flex: 1, marginBottom: 20 }}>
                            {/* Image Picker */}
                            <TouchableOpacity 
                                style={{ 
                                    width: 100, 
                                    height: 100, 
                                    borderRadius: 50, 
                                    backgroundColor: '#f1f5f9', 
                                    alignSelf: 'center', 
                                    justifyContent: 'center', 
                                    alignItems: 'center',
                                    marginBottom: 20,
                                    borderWidth: 2,
                                    borderColor: '#e2e8f0',
                                    borderStyle: 'dashed',
                                    overflow: 'hidden'
                                }}
                                onPress={async () => {
                                    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
                                    if (status !== 'granted') return;
                                    let result = await ImagePicker.launchImageLibraryAsync({
                                        mediaTypes: ['images'],
                                        allowsEditing: true,
                                        aspect: [1, 1],
                                        quality: 0.5,
                                        base64: true,
                                    });
                                    if (!result.canceled) {
                                        setNewPetData({ ...newPetData, image: `data:image/jpeg;base64,${result.assets[0].base64}` });
                                    }
                                }}
                            >
                                {newPetData.image ? (
                                    <Image source={{ uri: newPetData.image }} style={{ width: '100%', height: '100%' }} />
                                ) : (
                                    <>
                                        <Ionicons name="camera" size={32} color="#94a3b8" />
                                        <Text style={{ fontSize: 10, color: '#94a3b8', marginTop: 4 }}>Add Photo</Text>
                                    </>
                                )}
                            </TouchableOpacity>

                            <Text style={styles.formLabel}>Pet Name *</Text>
                            <TextInput 
                                style={styles.formInput} 
                                placeholder="e.g. Bella" 
                                value={newPetData.name} 
                                onChangeText={(text) => setNewPetData({ ...newPetData, name: text })} 
                            />

                            <Text style={styles.formLabel}>Pet Type</Text>
                            <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 20 }}>
                                {['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'].map(t => (
                                    <TouchableOpacity 
                                        key={t}
                                        onPress={() => setNewPetData({ ...newPetData, type: t })}
                                        style={[
                                            { paddingHorizontal: 16, paddingVertical: 8, borderRadius: 20, borderWidth: 1, borderColor: '#e2e8f0', backgroundColor: '#f8fafc' },
                                            newPetData.type === t && { borderColor: '#3b82f6', backgroundColor: '#eff6ff' }
                                        ]}
                                    >
                                        <Text style={[{ fontSize: 13, fontWeight: '600', color: '#64748b' }, newPetData.type === t && { color: '#3b82f6' }]}>{t}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>
                            
                            <Text style={styles.formLabel}>Breed</Text>
                            <TextInput 
                                style={styles.formInput} 
                                placeholder="e.g. Golden Retriever" 
                                value={newPetData.breed} 
                                onChangeText={(text) => setNewPetData({ ...newPetData, breed: text })} 
                            />
                            
                            <View style={{ flexDirection: 'row', gap: 15 }}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.formLabel}>Gender</Text>
                                    <View style={{ flexDirection: 'row', gap: 8, marginBottom: 20 }}>
                                        {['Male', 'Female'].map(g => (
                                            <TouchableOpacity 
                                                key={g} 
                                                style={[
                                                    { flex: 1, paddingVertical: 10, borderRadius: 12, borderWidth: 1, borderColor: '#e2e8f0', alignItems: 'center', backgroundColor: '#f8fafc' }, 
                                                    newPetData.gender === g && { borderColor: '#3b82f6', backgroundColor: '#eff6ff' }
                                                ]} 
                                                onPress={() => setNewPetData({ ...newPetData, gender: g })}
                                            >
                                                <Text style={[{ fontSize: 13, fontWeight: '600', color: '#64748b' }, newPetData.gender === g && { color: '#3b82f6' }]}>{g}</Text>
                                            </TouchableOpacity>
                                        ))}
                                    </View>
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.formLabel}>Age</Text>
                                    <TextInput 
                                        style={styles.formInput} 
                                        placeholder="e.g. 2 Years" 
                                        value={newPetData.age} 
                                        onChangeText={(text) => setNewPetData({ ...newPetData, age: text })} 
                                    />
                                </View>
                            </View>

                            <Text style={styles.formLabel}>Weight</Text>
                            <TextInput 
                                style={styles.formInput} 
                                placeholder="e.g. 5 kg" 
                                value={newPetData.weight} 
                                onChangeText={(text) => setNewPetData({ ...newPetData, weight: text })} 
                            />

                            <Text style={styles.formLabel}>Description</Text>
                            <TextInput 
                                style={[styles.formInput, { height: 100, textAlignVertical: 'top' }]} 
                                placeholder="Tell us about your pet..." 
                                multiline 
                                value={newPetData.description} 
                                onChangeText={(text) => setNewPetData({ ...newPetData, description: text })} 
                            />

                            <View style={{ height: 10 }} />

                            <TouchableOpacity
                                style={[styles.premiumBtn, isProcessing && { opacity: 0.7 }, { marginBottom: 30 }]}
                                onPress={handleAddPet}
                                disabled={isProcessing}
                            >
                                {isProcessing ? (
                                    <ActivityIndicator color="white" />
                                ) : (
                                    <LinearGradient
                                        colors={['#3b82f6', '#2563eb']}
                                        start={{ x: 0, y: 0 }}
                                        end={{ x: 1, y: 1 }}
                                        style={styles.premiumBtnGradient}
                                    >
                                        <Text style={styles.premiumBtnText}>Add to Family</Text>
                                        <Ionicons name="heart" size={18} color="white" style={{ marginLeft: 8 }} />
                                    </LinearGradient>
                                )}
                            </TouchableOpacity>
                        </ScrollView>
                    </View>
                </View>
            </Modal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#3b82f6',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 20,
    },
    loadingContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: '#f8fafc',
    },
    authBox: {
        width: '100%',
        backgroundColor: 'white',
        padding: 30,
        borderRadius: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 10,
        elevation: 8,
        alignItems: 'center',
    },
    logo: {
        width: 200,
        height: 100,
        marginBottom: 20,
    },
    title: {
        fontSize: 24,
        fontWeight: 'bold',
        marginBottom: 5,
        color: '#1e293b',
    },
    subtitle: {
        fontSize: 14,
        color: '#64748b',
        marginBottom: 25,
    },
    input: {
        width: '100%',
        height: 50,
        backgroundColor: '#f1f5f9',
        borderRadius: 12,
        paddingHorizontal: 15,
        marginBottom: 15,
        fontSize: 16,
    },
    button: {
        backgroundColor: '#3b82f6',
        width: '100%',
        height: 50,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 10,
        shadowColor: '#3b82f6',
        shadowOpacity: 0.4,
        shadowOffset: { width: 0, height: 4 },
        elevation: 4,
    },
    buttonText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 16,
    },
    premiumBtn: {
        backgroundColor: '#3b82f6',
        width: '100%',
        height: 56,
        borderRadius: 16,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#3b82f6',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 6,
    },
    premiumBtnSmall: {
        backgroundColor: '#3b82f6',
        paddingHorizontal: 16,
        paddingVertical: 10,
        borderRadius: 12,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#3b82f6',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 4,
        elevation: 3,
    },
    premiumBtnText: {
        color: 'white',
        fontWeight: '800',
        fontSize: 16,
        letterSpacing: 0.5,
    },
    premiumBtnTextSmall: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 14,
    },

    // Dashboard Styles
    dashboardContainer: {
        flex: 1,
        backgroundColor: '#f8fafc',
        paddingTop: 50,
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 20,
        marginBottom: 20,
        gap: 15,
    },
    menuBtn: {
        padding: 5,
    },
    iconBtn: {
        padding: 5,
        backgroundColor: '#f1f5f9',
        borderRadius: 20,
    },
    searchBar: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'white',
        height: 45,
        borderRadius: 25,
        paddingHorizontal: 15,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    content: {
        paddingHorizontal: 20,
        paddingBottom: 40,
    },
    heroSection: {
        height: 200,
        borderRadius: 24,
        overflow: 'hidden',
        marginBottom: 25,
        position: 'relative',
        backgroundColor: '#dbeafe',
    },
    heroBackground: {
        width: '100%',
        height: '100%',
        position: 'absolute',
    },
    heroOverlay: {
        position: 'absolute',
        bottom: 0,
        width: '100%',
        backgroundColor: 'rgba(255,255,255,0.6)',
        padding: 20,
        borderTopWidth: 1,
        borderColor: 'rgba(255,255,255,0.4)',
        // backdropFilter: 'blur(10px)', // iOS only - unsupported
    },
    greetingText: {
        fontSize: 28,
        fontWeight: '800',
        color: '#0f172a',
        marginBottom: 5,
    },
    heroSubText: {
        fontSize: 14,
        color: '#334155',
        lineHeight: 20,
    },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 15,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    linkText: {
        color: '#3b82f6',
        fontWeight: '600',
    },
    petsScroll: {
        marginBottom: 25,
    },
    petCard: {
        backgroundColor: 'white',
        padding: 15,
        borderRadius: 20,
        alignItems: 'center',
        marginRight: 15,
        borderWidth: 1,
        borderColor: '#f1f5f9',
        width: 110,
        shadowColor: '#000',
        shadowOpacity: 0.05,
        shadowOffset: { width: 0, height: 2 },
        elevation: 2,
    },
    addPetCard: {
        backgroundColor: '#f8fafc',
        padding: 15,
        borderRadius: 20,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 15,
        borderWidth: 2,
        borderColor: '#e2e8f0',
        borderStyle: 'dashed',
        width: 110,
        height: 140,
    },
    petImage: {
        width: 60,
        height: 60,
        borderRadius: 30,
        marginBottom: 10,
        backgroundColor: '#e2e8f0',
    },
    petName: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 2,
        textAlign: 'center',
    },
    petBreed: {
        fontSize: 11,
        color: '#94a3b8',
        textAlign: 'center',
    },
    lostBadge: {
        position: 'absolute',
        top: 10,
        right: 10,
        backgroundColor: '#fee2e2',
        color: '#ef4444',
        fontSize: 8,
        fontWeight: 'bold',
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 8,
    },
    gridContainer: {
        gap: 20,
    },
    card: {
        backgroundColor: 'white',
        borderRadius: 20,
        padding: 20,
        shadowColor: '#000',
        shadowOpacity: 0.05,
        shadowOffset: { width: 0, height: 2 },
        elevation: 2,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 15,
    },
    iconTitle: {
        flexDirection: 'row',
        gap: 12,
        alignItems: 'center',
    },
    iconBox: {
        width: 36,
        height: 36,
        borderRadius: 10,
        alignItems: 'center',
        justifyContent: 'center',
    },
    cardTitle: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    cardSub: {
        fontSize: 12,
        color: '#64748b',
    },
    scheduleItem: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    schedMeal: {
        fontWeight: '600',
        color: '#334155',
    },
    schedPet: {
        fontSize: 12,
        color: '#94a3b8',
    },
    schedTime: {
        color: '#10b981',
        fontWeight: 'bold',
    },
    emptyText: {
        textAlign: 'center',
        color: '#94a3b8',
        padding: 10,
        fontStyle: 'italic',
    },
    apptItem: {
        flexDirection: 'row',
        gap: 12,
        marginBottom: 12,
        alignItems: 'center',
        backgroundColor: '#f8fafc',
        padding: 10,
        borderRadius: 12,
    },
    dateBox: {
        backgroundColor: 'white',
        paddingHorizontal: 10,
        paddingVertical: 5,
        borderRadius: 8,
        alignItems: 'center',
        minWidth: 45,
    },
    dateNum: {
        fontWeight: 'bold',
        color: '#334155',
    },
    apptTitle: {
        fontWeight: '600',
        fontSize: 14,
        color: '#1e293b',
    },
    apptPet: {
        fontSize: 12,
        color: '#64748b',
    },
    lostBanner: {
        backgroundColor: '#fff1f2',
        borderWidth: 1,
        borderColor: '#fecaca',
        borderRadius: 20,
        padding: 15,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 15,
        marginBottom: 20,
    },
    bannerIcon: {
        backgroundColor: '#ef4444',
        width: 45,
        height: 45,
        borderRadius: 25,
        alignItems: 'center',
        justifyContent: 'center',
    },
    bannerTitle: {
        color: '#991b1b',
        fontWeight: 'bold',
        fontSize: 14,
    },
    bannerSubtitle: {
        color: '#b91c1c',
        fontSize: 12,
    },
    lostItem: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 12,
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#fee2e2',
    },
    lostItemImg: {
        width: 45,
        height: 45,
        borderRadius: 10,
    },
    lostItemName: {
        fontWeight: 'bold',
        color: '#991b1b',
        fontSize: 13,
    },
    lostItemLoc: {
        fontSize: 11,
        color: '#b91c1c',
    },
    sightingBtn: {
        backgroundColor: '#ef4444',
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 8,
    },
    sightingBtnText: {
        color: 'white',
        fontSize: 11,
        fontWeight: 'bold',
    },
    sidebarOverlay: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: 'rgba(0,0,0,0.5)',
        zIndex: 1000,
        flexDirection: 'row',
    },
    sidebarContainer: {
        width: '80%',
        backgroundColor: 'white',
        height: '100%',
        paddingTop: 50,
        paddingBottom: 20,
        shadowColor: "#000",
        shadowOffset: {
            width: 0,
            height: 2,
        },
        shadowOpacity: 0.25,
        shadowRadius: 3.84,
        elevation: 5,
    },
    sidebarHeader: {
        paddingHorizontal: 20,
        paddingBottom: 20,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    sidebarTitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    sidebarContent: {
        flex: 1,
        paddingVertical: 10,
    },
    sidebarItem: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 15,
        paddingHorizontal: 20,
        gap: 15,
        marginHorizontal: 10,
    },
    sidebarItemActive: {
        backgroundColor: '#3b82f6',
        borderRadius: 12,
    },
    sidebarText: {
        fontSize: 16,
        color: '#475569',
        fontWeight: '500',
    },
    sidebarTextActive: {
        color: 'white',
        fontWeight: 'bold',
    },
    sidebarFooter: {
        paddingVertical: 10,
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
    },

    // Adoption Screen Styles
    pageTitle: {
        fontSize: 24,
        fontWeight: '800', // Outfit-Bold eq
        color: '#0f172a',
        marginBottom: 20,
    },
    filterScroll: {
        marginBottom: 25,
        maxHeight: 50,
    },
    filterTab: {
        paddingHorizontal: 20,
        paddingVertical: 10,
        backgroundColor: 'white',
        borderRadius: 25,
        marginRight: 10,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        height: 40,
        justifyContent: 'center',
    },
    filterTabActive: {
        backgroundColor: 'white',
        borderColor: '#111827',
        borderWidth: 2, // Thicker border for active state
    },
    filterText: {
        fontSize: 13,
        fontWeight: '600',
        color: '#64748b',
    },
    filterTextActive: {
        color: '#111827',
        fontWeight: '700',
    },
    adoptionSearchContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'white',
        borderRadius: 25,
        paddingHorizontal: 15,
        height: 50,
        marginBottom: 20,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        shadowColor: '#000',
        shadowOpacity: 0.05,
        shadowOffset: { width: 0, height: 2 },
        elevation: 1,
    },
    adoptionSearchInput: {
        flex: 1,
        marginLeft: 10,
        fontSize: 14,
        color: '#1e293b',
    },
    listingsGrid: {
        gap: 20,
    },
    adoptionCard: {
        backgroundColor: 'white',
        borderRadius: 24,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: '#f1f5f9',
        shadowColor: '#000',
        shadowOpacity: 0.05,
        shadowOffset: { width: 0, height: 4 },
        shadowRadius: 15,
        elevation: 4,
        marginBottom: 20,
        marginHorizontal: 15,
    },
    adoptionImage: {
        width: '100%',
        height: 230,
        backgroundColor: '#f8fafc',
    },
    adoptionInfo: {
        padding: 20,
    },
    adoptionHeaderRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 10,
    },
    adoptionName: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#111827',
        fontFamily: Platform.OS === 'ios' ? 'System' : 'sans-serif-medium',
    },
    typeTag: {
        backgroundColor: '#dbeafe',
        paddingHorizontal: 12,
        paddingVertical: 5,
        borderRadius: 12,
    },
    typeTagText: {
        fontSize: 11,
        fontWeight: '700',
        color: '#1e40af',
    },
    adoptionDetailsRow: {
        marginBottom: 20,
    },
    adoptionDetails: {
        fontSize: 15,
        color: '#6b7280',
    },
    greenProfileBtn: {
        backgroundColor: '#10b981',
        paddingVertical: 14,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
    },
    greenProfileBtnText: {
        color: 'white',
        fontSize: 16,
        fontWeight: '700',
    },

    // New Screen Styles
    addBtnSmall: {
        backgroundColor: '#3b82f6',
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 8,
        gap: 5,
    },
    petCardWide: {
        backgroundColor: 'white',
        borderRadius: 20,
        padding: 15,
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#f1f5f9',
        marginBottom: 15,
    },
    petImageLarge: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: '#e2e8f0',
    },
    petNameLarge: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    petDetailsLarge: {
        color: '#64748b',
        fontSize: 13,
        marginBottom: 10,
    },
    manageBtn: {
        backgroundColor: '#eff6ff',
        paddingVertical: 6,
        paddingHorizontal: 15,
        borderRadius: 8,
        alignSelf: 'flex-start',
    },
    manageBtnText: {
        color: '#3b82f6',
        fontWeight: 'bold',
        fontSize: 12,
    },

    // Feeder Styles
    feederCard: {
        backgroundColor: 'white',
        borderRadius: 24,
        padding: 25,
        borderWidth: 1,
        borderColor: '#f1f5f9',
        alignItems: 'center',
    },
    statusRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        width: '100%',
        marginBottom: 20,
    },
    statusLabel: {
        color: '#64748b',
        fontWeight: '600',
    },
    statusBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#ecfdf5',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 12,
        gap: 6,
    },
    statusDot: {
        width: 8,
        height: 8,
        borderRadius: 4,
    },
    statusText: {
        color: '#10b981',
        fontWeight: 'bold',
        fontSize: 12,
    },
    feederHeroImg: {
        width: 150,
        height: 150,
        marginBottom: 20,
    },
    portionTitle: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 15,
    },
    portionRow: {
        flexDirection: 'row',
        gap: 10,
        marginBottom: 30,
    },
    portionBtn: {
        paddingHorizontal: 15,
        paddingVertical: 10,
        borderRadius: 12,
        backgroundColor: '#f8fafc',
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    portionBtnActive: {
        backgroundColor: '#3b82f6',
        borderColor: '#3b82f6',
    },
    portionBtnText: {
        color: '#64748b',
        fontWeight: '600',
    },
    portionBtnTextActive: {
        color: 'white',
    },
    feedNowBtn: {
        backgroundColor: '#3b82f6',
        flexDirection: 'row',
        alignItems: 'center',
        width: '100%',
        justifyContent: 'center',
        paddingVertical: 15,
        borderRadius: 16,
    },
    feedNowText: {
        color: 'white',
        fontSize: 18,
        fontWeight: 'bold',
    },

    // Marketplace Styles
    marketplaceGrid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: 15,
    },
    productCard: {
        width: '47%',
        backgroundColor: 'white',
        borderRadius: 16,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    productImage: {
        width: '100%',
        height: 120,
        backgroundColor: '#f8fafc',
    },
    productInfo: {
        padding: 12,
    },
    productName: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 4,
    },
    productPrice: {
        fontSize: 16,
        fontWeight: '800',
        color: '#10b981',
        marginBottom: 10,
    },
    buyBtn: {
        backgroundColor: '#3b82f6',
        paddingVertical: 8,
        borderRadius: 8,
        alignItems: 'center',
    },
    buyBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 12,
    },

    // Refined My Pets Grid Styles (My Family)
    petGridRow: {
        flexDirection: 'column',
        gap: 15,
        paddingBottom: 20,
    },
    petGridCard: {
        width: '100%',
        backgroundColor: 'white',
        borderRadius: 24,
        padding: 20,
        borderWidth: 1,
        borderColor: '#f1f5f9',
        shadowColor: '#000',
        shadowOpacity: 0.05,
        shadowOffset: { width: 0, height: 4 },
        shadowRadius: 10,
        elevation: 2,
    },
    petCardContent: {
        flexDirection: 'row',
        alignItems: 'center',
        width: '100%',
    },
    petImageCircle: {
        width: 100,
        height: 100,
        borderRadius: 50,
        backgroundColor: '#f8fafc',
    },
    petInfoSection: {
        flex: 1,
        marginLeft: 20,
    },
    petNameText: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 2,
    },
    petBreedText: {
        fontSize: 14,
        color: '#64748b',
        marginBottom: 12,
    },
    petActionRow: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        alignItems: 'center',
        gap: 8,
    },
    actionBtn: {
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 8,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        backgroundColor: '#f8fafc',
    },
    actionBtnText: {
        fontSize: 12,
        fontWeight: '600',
        color: '#1e293b',
    },
    reportLostBtnSmall: {
        borderColor: '#fee2e2',
        backgroundColor: '#fef2f2',
    },
    reportLostTextSmall: {
        color: '#ef4444',
    },
    deleteBtnSmall: {
        padding: 8,
        borderRadius: 8,
        borderWidth: 1,
        borderColor: '#fee2e2',
        backgroundColor: '#fef2f2',
        alignItems: 'center',
        justifyContent: 'center',
    },
    petGridAddCard: {
        width: '100%',
        height: 120,
        backgroundColor: 'white',
        borderRadius: 24,
        borderWidth: 2,
        borderColor: '#e2e8f0',
        borderStyle: 'dashed',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 15,
    },
    addPetCircle: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: '#cbd5e1',
        alignItems: 'center',
        justifyContent: 'center',
    },
    addPetLinkText: {
        fontSize: 16,
        fontWeight: '600',
        color: '#64748b',
    },

    // Modal Styles
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'flex-end',
    },
    profileModalContent: {
        backgroundColor: 'white',
        borderTopLeftRadius: 32,
        borderTopRightRadius: 32,
        height: '85%',
        width: '100%',
    },
    closeBtn: {
        position: 'absolute',
        top: 20,
        right: 20,
        zIndex: 10,
        backgroundColor: '#f1f5f9',
        width: 40,
        height: 40,
        borderRadius: 20,
        alignItems: 'center',
        justifyContent: 'center',
    },
    modalPetImg: {
        width: '100%',
        height: 300,
        backgroundColor: '#f1f5f9',
    },
    modalInfoPadding: {
        padding: 24,
    },
    badgeRow: {
        flexDirection: 'row',
        gap: 8,
        marginBottom: 12,
    },
    petTypeBadge: {
        backgroundColor: '#eff6ff',
        color: '#3b82f6',
        paddingHorizontal: 12,
        paddingVertical: 4,
        borderRadius: 12,
        fontSize: 10,
        fontWeight: 'bold',
        textTransform: 'uppercase',
    },
    lostBadge: {
        backgroundColor: '#fee2e2',
        color: '#ef4444',
        paddingHorizontal: 12,
        paddingVertical: 4,
        borderRadius: 12,
        fontSize: 10,
        fontWeight: 'bold',
        textTransform: 'uppercase',
    },
    modalPetName: {
        fontSize: 32,
        fontWeight: 'bold',
        color: '#1e293b',
        // fontFamily: 'Outfit',
    },
    modalPetSub: {
        fontSize: 16,
        color: '#64748b',
        marginBottom: 24,
    },
    aboutSection: {
        marginBottom: 24,
    },
    sectionTitleSmall: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 8,
    },
    aboutText: {
        fontSize: 14,
        color: '#475569',
        lineHeight: 22,
    },
    statsRow: {
        flexDirection: 'row',
        gap: 12,
        marginBottom: 32,
    },
    statBox: {
        flex: 1,
        backgroundColor: '#f8fafc',
        padding: 16,
        borderRadius: 16,
        borderWidth: 1,
        borderColor: '#f1f5f9',
        alignItems: 'center',
    },
    statLabel: {
        fontSize: 10,
        color: '#94a3b8',
        fontWeight: 'bold',
        marginBottom: 4,
    },
    statValue: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    modalActionGrid: {
        flexDirection: 'row',
        gap: 12,
    },
    modalActionBtn: {
        flex: 1,
        paddingVertical: 16,
        borderRadius: 16,
        alignItems: 'center',
    },
    modalActionTextWhite: {
        color: 'white',
        fontWeight: 'bold',
    },
    modalActionTextGray: {
        color: '#475569',
        fontWeight: 'bold',
    },
    // Form Styles for Rehoming
    formInput: {
        width: '100%',
        height: 50,
        backgroundColor: '#f8fafc',
        borderRadius: 12,
        paddingHorizontal: 15,
        marginBottom: 15,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        fontSize: 14,
        color: '#1e293b',
    },
    formSelectBtn: {
        flex: 1,
        paddingVertical: 12,
        backgroundColor: '#f8fafc',
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        alignItems: 'center',
        marginBottom: 15,
    },
    formSelectBtnActive: {
        backgroundColor: '#3b82f6',
        borderColor: '#3b82f6',
    },
    formSelectText: {
        color: '#64748b',
        fontWeight: 'bold',
    },
    formSelectTextActive: {
        color: 'white',
    },
    // Rehoming Dashboard Styles
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 8,
    },
    addBtnRehome: {
        backgroundColor: '#3b82f6',
        paddingHorizontal: 12,
        paddingVertical: 8,
        borderRadius: 8,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
    },
    addBtnTextRehome: {
        color: 'white',
        fontSize: 12,
        fontWeight: 'bold',
    },
    pageSubtitle: {
        fontSize: 14,
        color: '#64748b',
        marginBottom: 20,
    },
    rehomeEntryCard: {
        backgroundColor: 'white',
        borderRadius: 16,
        padding: 12,
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#f1f5f9',
        shadowColor: '#64748b',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 8,
        elevation: 2,
    },
    rehomeEntryImg: {
        width: 80,
        height: 80,
        borderRadius: 12,
        backgroundColor: '#f8fafc',
    },
    rehomeEntryInfo: {
        flex: 1,
        marginLeft: 15,
    },
    rehomeEntryHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 4,
    },
    rehomeEntryName: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    rehomeEntrySub: {
        fontSize: 13,
        color: '#64748b',
        marginBottom: 4,
    },
    rehomeEntryDate: {
        fontSize: 11,
        color: '#94a3b8',
    },
    rehomeDeleteBtn: {
        width: 36,
        height: 36,
        borderRadius: 10,
        backgroundColor: '#fef2f2',
        alignItems: 'center',
        justifyContent: 'center',
    },
    viewProfileBtnRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'flex-end',
        marginTop: 5,
    },
    viewProfileBtnText: {
        color: '#3b82f6',
        fontWeight: 'bold',
        fontSize: 14,
        marginRight: 4,
    },
    premiumBtnGradient: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        width: '100%',
        height: '100%',
        borderRadius: 16,
    },
    stylishAddBtn: {
        borderRadius: 14,
        overflow: 'hidden',
        elevation: 4,
        shadowColor: '#10b981',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
    },
    stylishAddBtnGradient: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 10,
    },
    stylishAddBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 15,
    },
    statusBadgeSmall: {
        paddingHorizontal: 8,
        paddingVertical: 2,
        borderRadius: 12,
    },
    statusBadgeTextSmall: {
        fontSize: 9,
        fontWeight: '800',
    },
    statusPending: { backgroundColor: '#fef3c7' },
    statusActive: { backgroundColor: '#dcfce7' },
    statusAdopted: { backgroundColor: '#dbeafe' },
    statusRejected: { backgroundColor: '#fee2e2' },
    statusBadgeTextSmall_Pending: { color: '#b45309' },
    statusBadgeTextSmall_Active: { color: '#166534' },
    statusBadgeTextSmall_Adopted: { color: '#1e40af' },
    statusBadgeTextSmall_Rejected: { color: '#991b1b' },
    linkText: {
        color: '#3b82f6',
        fontWeight: 'bold',
        fontSize: 14,
    },
    emptyStateContainer: {
        alignItems: 'center',
        paddingVertical: 50,
        gap: 15,
    },
    emptyStateText: {
        color: '#94a3b8',
        fontSize: 16,
    },
    // New Smart Feeder Styles
    minimalCard: {
        background: 'white',
        borderRadius: 24,
        padding: 20,
        backgroundColor: 'white',
        shadowColor: '#000',
        shadowOpacity: 0.05,
        shadowOffset: { width: 0, height: 4 },
        elevation: 2,
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    feederCardTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
        fontFamily: 'System', // Use System as fallback for Outfit
    },
    feederCardSubtitle: {
        fontSize: 13,
        color: '#64748b',
        marginTop: 2,
    },
    statusBadgeGreen: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#ecfdf5',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 20,
        gap: 6,
    },
    dotGreen: {
        width: 6,
        height: 6,
        borderRadius: 3,
        backgroundColor: '#10b981',
    },
    statusTextGreen: {
        fontSize: 10,
        fontWeight: 'bold',
        color: '#10b981',
    },
    feederStatusRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        backgroundColor: '#f8fafc',
        padding: 12,
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        marginBottom: 20,
    },
    feederStatusText: {
        fontSize: 12,
        color: '#1e293b',
        fontWeight: '500',
    },
    formLabelFeeder: {
        fontSize: 13,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 10,
    },
    feederSelectContainer: {
        marginBottom: 15,
    },
    miniPetTab: {
        paddingHorizontal: 15,
        paddingVertical: 8,
        backgroundColor: '#f1f5f9',
        borderRadius: 10,
        marginRight: 8,
    },
    miniPetTabActive: {
        backgroundColor: '#3b82f6',
    },
    miniPetTabText: {
        fontSize: 13,
        color: '#64748b',
        fontWeight: '600',
    },
    miniPetTabTextActive: {
        color: 'white',
    },
    portionGridFeeder: {
        flexDirection: 'row',
        gap: 10,
        marginBottom: 20,
    },
    portionOptionFeeder: {
        flex: 1,
        padding: 12,
        borderRadius: 12,
        borderWidth: 2,
        borderColor: '#f1f5f9',
        alignItems: 'center',
        backgroundColor: 'white',
    },
    portionOptionFeederActive: {
        borderColor: '#3b82f6',
        backgroundColor: '#eff6ff',
    },
    portionOptionTitle: {
        fontWeight: 'bold',
        color: '#1e293b',
        fontSize: 14,
    },
    portionOptionTitleActive: {
        color: '#3b82f6',
    },
    portionOptionGrams: {
        fontSize: 12,
        color: '#64748b',
        marginTop: 2,
    },
    btnFeedNowFeeder: {
        backgroundColor: '#3b82f6',
        paddingVertical: 15,
        borderRadius: 16,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        shadowColor: '#3b82f6',
        shadowOpacity: 0.3,
        shadowOffset: { width: 0, height: 4 },
        elevation: 4,
    },
    btnFeedNowTextFeeder: {
        color: 'white',
        fontSize: 16,
        fontWeight: 'bold',
    },
    feederInputSmall: {
        backgroundColor: '#f8fafc',
        borderWidth: 1,
        borderColor: '#e2e8f0',
        borderRadius: 10,
        paddingHorizontal: 12,
        paddingVertical: 10,
        fontSize: 14,
    },
    btnSaveSchedFeeder: {
        backgroundColor: '#1e293b',
        paddingVertical: 12,
        borderRadius: 12,
        alignItems: 'center',
    },
    btnSaveSchedTextFeeder: {
        color: 'white',
        fontWeight: 'bold',
    },
    feederHistoryItem: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    feederHistoryName: {
        fontWeight: 'bold',
        fontSize: 14,
        color: '#1e293b',
    },
    feederHistoryTime: {
        fontSize: 12,
        color: '#64748b',
    },
    feederHistoryPortion: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    successBadgeSmall: {
        backgroundColor: '#dcfce7',
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
        marginTop: 4,
    },
    successBadgeTextSmall: {
        color: '#166534',
        fontSize: 8,
        fontWeight: 'bold',
    },
    emptyTextFeeder: {
        textAlign: 'center',
        padding: 20,
        color: '#94a3b8',
        fontSize: 14,
    },
    // Schedule Styles
    stepBadge: {
        backgroundColor: '#f1f5f9',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    stepBadgeText: {
        fontSize: 10,
        color: '#64748b',
        fontWeight: 'bold',
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 20,
        paddingTop: Platform.OS === 'ios' ? 60 : 20,
        paddingBottom: 15,
        backgroundColor: 'white',
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    searchBar: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'white',
        marginHorizontal: 15,
        paddingHorizontal: 15,
        height: 48,
        borderRadius: 24,
        borderWidth: 1.5,
        borderColor: '#e2e8f0',
        elevation: 2,
        shadowColor: '#3b82f6',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
    },
    menuBtn: {
        width: 44,
        height: 44,
        borderRadius: 22,
        alignItems: 'center',
        justifyContent: 'center',
    },
    iconBtn: {
        width: 44,
        height: 44,
        borderRadius: 22,
        alignItems: 'center',
        justifyContent: 'center',
    },
    formLabelSchedule: {
        fontSize: 12,
        fontWeight: '700',
        color: '#475569',
        letterSpacing: 0.5,
        marginBottom: 10,
        marginTop: 10,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        textTransform: 'uppercase'
    },
    categoryGridSchedule: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        justifyContent: 'space-between',
        paddingVertical: 10,
        marginBottom: 20,
    },
    categoryCardSchedule: {
        width: '30%',
        backgroundColor: 'white',
        borderRadius: 20,
        paddingVertical: 22,
        paddingHorizontal: 5,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 1,
        borderColor: '#f1f5f9',
        marginBottom: 15,
        elevation: 3,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 8,
    },
    categoryCardScheduleActive: {
        borderColor: '#3b82f6',
        backgroundColor: '#ffffff',
        borderWidth: 2,
    },
    categoryIconCircle: {
        width: 50,
        height: 50,
        borderRadius: 15,
        backgroundColor: '#f8fafc',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 12,
    },
    categoryIconCircleActive: {
        backgroundColor: '#eff6ff',
    },
    categoryTitleSchedule: {
        fontSize: 10,
        textAlign: 'center',
        color: '#64748b',
        fontWeight: '600',
        lineHeight: 14,
        paddingHorizontal: 2
    },
    categoryTitleScheduleActive: {
        color: '#3b82f6',
        fontWeight: '700'
    },
    serviceHorizontalScroll: {
        flexDirection: 'row',
        paddingVertical: 10,
        paddingRight: 20
    },
    serviceCardSmall: {
        backgroundColor: 'white',
        borderWidth: 1.5,
        borderColor: '#f1f5f9',
        borderRadius: 12,
        padding: 16,
        marginRight: 12,
        width: 140,
        justifyContent: 'center',
        elevation: 2,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 4,
    },
    serviceCardSmallActive: {
        borderColor: '#3b82f6',
        backgroundColor: '#eff6ff',
    },
    serviceNameSmall: {
        fontSize: 12,
        fontWeight: '700',
        color: '#1e293b',
        marginBottom: 4,
    },
    serviceDurationSmall: {
        fontSize: 10,
        color: '#94a3b8',
        position: 'absolute',
        top: 10,
        right: 10,
    },
    scheduleSectionTitle: {
        fontSize: 12,
        fontWeight: '800',
        color: '#475569',
        letterSpacing: 1,
        marginBottom: 15,
        marginTop: 20,
        textTransform: 'uppercase',
    },
    clinicCardWide: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 18,
        backgroundColor: 'white',
        borderRadius: 20,
        borderWidth: 1.5,
        borderColor: '#f1f5f9',
        marginBottom: 15,
        elevation: 4,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.06,
        shadowRadius: 10,
    },
    clinicCardWideActive: {
        borderColor: '#3b82f6',
        backgroundColor: '#eff6ff',
        borderWidth: 2,
    },
    clinicLogoCircle: {
        width: 50,
        height: 50,
        borderRadius: 25,
        backgroundColor: '#f8fafc',
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    clinicInfoMain: {
        flex: 1,
        marginLeft: 15,
    },
    clinicNameLarge: {
        fontSize: 15,
        fontWeight: '700',
        color: '#1e293b',
    },
    clinicSubText: {
        fontSize: 11,
        color: '#64748b',
        marginTop: 4,
    },
    clinicPriceTag: {
        fontSize: 16,
        fontWeight: '800',
        color: '#10b981',
    },
    datePickerStatic: {
        backgroundColor: 'white',
        borderRadius: 14,
        padding: 16,
        borderWidth: 1.5,
        borderColor: '#e2e8f0',
        marginBottom: 20,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between'
    },
    slotBtnText: {
        fontSize: 12,
        fontWeight: '700',
        color: '#1e293b',
    },
    slotBtnTextActive: {
        color: '#3b82f6',
    },
    clinicAlertBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#eff6ff',
        padding: 14,
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#dbeafe',
        marginBottom: 25,
    },
    clinicAlertText: {
        fontSize: 12,
        color: '#1d4ed8',
        fontWeight: '600',
        marginLeft: 10
    },
    estimationRowFull: {
        marginBottom: 15,
    },
    secureBookBtn: {
        backgroundColor: '#334155',
        height: 56,
        borderRadius: 16,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 20,
        elevation: 8,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
    },
    secureBookBtnText: {
        color: 'white',
        fontSize: 16,
        fontWeight: '700',
        marginLeft: 10,
    },
    slotBtnTextActive: {
        color: '#3b82f6',
    },
    clinicAlertBadge: {
        backgroundColor: '#eff6ff',
        padding: 12,
        borderRadius: 12,
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 25,
        gap: 10,
    },
    clinicAlertText: {
        fontSize: 12,
        color: '#3b82f6',
        fontWeight: '700',
    },
    scheduleFooterFull: {
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
        paddingTop: 24,
        marginTop: 10,
    },
    estimationRowFull: {
        marginBottom: 20,
    },
    serviceHorizontalScroll: {
        flexDirection: 'row',
        gap: 12,
        marginBottom: 25,
    },
    serviceCardSmall: {
        backgroundColor: 'white',
        borderWidth: 1,
        borderColor: '#f1f5f9',
        borderRadius: 12,
        padding: 15,
        minWidth: 140,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    serviceCardSmallActive: {
        borderColor: '#3b82f6',
        backgroundColor: '#eff6ff',
    },
    serviceNameSmall: {
        fontSize: 12,
        fontWeight: '700',
        color: '#1e293b',
        maxWidth: 100,
    },
    serviceDurationSmall: {
        fontSize: 10,
        color: '#94a3b8',
    },
    scheduleFooter: {
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
        paddingTop: 20,
        marginTop: 10,
    },
    estimationLabelLarge: {
        fontSize: 12,
        color: '#64748b',
        marginBottom: 4,
    },
    estimationValueLarge: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 15,
    },
    secureBookBtn: {
        backgroundColor: '#0f172a',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 15,
        borderRadius: 12,
        gap: 10,
    },
    secureBookBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 15,
    },
    estimationRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: 10,
        paddingTop: 20,
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
    },
    estimationLabel: {
        fontSize: 12,
        color: '#64748b',
        marginBottom: 2,
    },
    estimationValue: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    btnSecureBooking: {
        backgroundColor: '#1e293b',
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 12,
        borderRadius: 12,
        gap: 8,
    },
    btnSecureBookingText: {
        color: 'white',
        fontSize: 13,
        fontWeight: 'bold',
    },
    // Multi-step Schedule Styles
    scheduleHeaderRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 10,
    },
    backLink: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 15,
        gap: 4,
    },
    backLinkText: {
        color: '#3b82f6',
        fontSize: 13,
        fontWeight: '600',
    },
    subServiceCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f8fafc',
        padding: 15,
        borderRadius: 14,
        marginBottom: 10,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    subServiceCardActive: {
        borderColor: '#3b82f6',
        backgroundColor: '#eff6ff',
    },
    subServiceName: {
        fontSize: 15,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    subServiceDesc: {
        fontSize: 12,
        color: '#64748b',
        marginTop: 2,
    },
    subServiceDuration: {
        fontSize: 12,
        color: '#3b82f6',
        fontWeight: 'bold',
    },
    clinicCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f8fafc',
        padding: 12,
        borderRadius: 16,
        marginBottom: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    clinicCardActive: {
        borderColor: '#3b82f6',
        borderWidth: 2,
        backgroundColor: 'white',
    },
    clinicIconBox: {
        width: 50,
        height: 50,
        borderRadius: 12,
        backgroundColor: '#eff6ff',
        justifyContent: 'center',
        alignItems: 'center',
    },
    clinicName: {
        fontSize: 15,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    clinicAddress: {
        fontSize: 12,
        color: '#64748b',
    },
    ratingRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 2,
        gap: 4,
    },
    ratingText: {
        fontSize: 12,
        color: '#1e293b',
        fontWeight: 'bold',
    },
    clinicPriceBox: {
        paddingHorizontal: 10,
        paddingVertical: 4,
        backgroundColor: '#f1f5f9',
        borderRadius: 8,
    },
    clinicPriceText: {
        fontSize: 13,
        fontWeight: 'bold',
        color: '#10b981',
    },
    slotGrid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: 10,
    },
    slotBox: {
        width: '30%',
        paddingVertical: 12,
        borderRadius: 10,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        alignItems: 'center',
        backgroundColor: 'white',
    },
    slotBoxSelected: {
        backgroundColor: '#3b82f6',
        borderColor: '#3b82f6',
    },
    slotBoxDisabled: {
        backgroundColor: '#f1f5f9',
        borderColor: '#f1f5f9',
        opacity: 0.5,
    },
    slotText: {
        fontSize: 12,
        color: '#64748b',
        fontWeight: '600',
    },
    slotTextSelected: {
        color: 'white',
    },
    slotTextDisabled: {
        color: '#94a3b8',
    },
    summaryBox: {
        backgroundColor: '#f8fafc',
        borderRadius: 16,
        padding: 15,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        marginBottom: 20,
    },
    summaryRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 8,
    },
    summaryLabel: {
        fontSize: 13,
        color: '#64748b',
    },
    summaryValue: {
        fontSize: 13,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    totalPayableLabel: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    totalPayableValue: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    btnSecureBookingFull: {
        backgroundColor: '#1e293b',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 16,
        borderRadius: 14,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 5,
        elevation: 6,
    },
    // Payment Modal Styles
    paymentCard: {
        backgroundColor: 'white',
        borderRadius: 24,
        padding: 20,
        width: '100%',
        maxWidth: 400,
    },
    paymentHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 20,
    },
    paymentTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    paymentAmountBox: {
        backgroundColor: '#f8fafc',
        padding: 20,
        borderRadius: 16,
        alignItems: 'center',
        marginBottom: 20,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    amountLabel: {
        fontSize: 13,
        color: '#64748b',
        marginBottom: 4,
    },
    amountValue: {
        fontSize: 28,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    paymentMethods: {
        marginBottom: 20,
    },
    paymentOption: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 15,
        backgroundColor: '#f8fafc',
        borderRadius: 12,
        marginTop: 10,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        gap: 12,
    },
    paymentOptionText: {
        flex: 1,
        fontSize: 14,
        fontWeight: '600',
        color: '#1e293b',
    },
    payNowBtn: {
        backgroundColor: '#3b82f6',
        paddingVertical: 16,
        borderRadius: 14,
        alignItems: 'center',
        marginBottom: 10,
    },
    payNowBtnText: {
        color: 'white',
        fontSize: 16,
        fontWeight: 'bold',
    },
    payNowBtnDisabled: {
        opacity: 0.7,
    },
    secureText: {
        textAlign: 'center',
        fontSize: 11,
        color: '#94a3b8',
    },
    paymentSuccessContent: {
        alignItems: 'center',
        paddingVertical: 30,
    },
    successIconCircle: {
        width: 100,
        height: 100,
        borderRadius: 50,
        backgroundColor: '#ecfdf5',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 20,
    },
    paymentSuccessTitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 8,
    },
    paymentSuccessSub: {
        fontSize: 14,
        color: '#64748b',
        textAlign: 'center',
    },
    // New Screens Utilities
    emptyCardCentered: {
        backgroundColor: 'white',
        borderRadius: 24,
        padding: 40,
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 40,
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    marketplaceGoBtn: {
        marginTop: 20,
        backgroundColor: '#3b82f6',
        paddingHorizontal: 25,
        paddingVertical: 12,
        borderRadius: 12,
    },
    marketplaceGoText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 14,
    },
    lostAlertCardSmall: {
        flexDirection: 'row',
        backgroundColor: 'white',
        borderRadius: 16,
        marginBottom: 15,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    lostAlertImgSmall: {
        width: 100,
        height: 100,
        backgroundColor: '#f8fafc',
    },
    lostAlertNameSmall: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    lostAlertLocSmall: {
        fontSize: 12,
        color: '#64748b',
        marginTop: 4,
    },
    lostStatusSmall: {
        marginTop: 8,
        backgroundColor: '#fee2e2',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 6,
        alignSelf: 'flex-start',
    },
    lostStatusTextSmall: {
        color: '#ef4444',
        fontSize: 10,
        fontWeight: 'bold',
    },

    // Marketplace & Cart Styles
    marketplaceHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 15,
    },
    cartBtn: {
        position: 'relative',
        padding: 8,
        backgroundColor: 'white',
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    cartBadge: {
        position: 'absolute',
        top: -5,
        right: -5,
        backgroundColor: '#ef4444',
        borderRadius: 10,
        minWidth: 20,
        height: 20,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: 'white',
    },
    cartBadgeText: {
        color: 'white',
        fontSize: 10,
        fontWeight: 'bold',
    },
    searchContainerMarket: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'white',
        borderRadius: 16,
        paddingHorizontal: 15,
        height: 50,
        marginBottom: 20,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    searchInputMarket: {
        flex: 1,
        marginLeft: 10,
        fontSize: 14,
        color: '#1e293b',
    },
    categoryChipsScroll: {
        marginBottom: 25,
        maxHeight: 45,
    },
    categoryChip: {
        paddingHorizontal: 18,
        paddingVertical: 10,
        backgroundColor: 'white',
        borderRadius: 12,
        marginRight: 10,
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    categoryChipActive: {
        backgroundColor: '#1e293b',
        borderColor: '#1e293b',
    },
    categoryChipText: {
        fontSize: 13,
        fontWeight: '600',
        color: '#64748b',
    },
    categoryChipTextActive: {
        color: 'white',
    },
    marketCard: {
        width: '47.5%',
        backgroundColor: 'white',
        borderRadius: 20,
        marginBottom: 15,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    marketCardImage: {
        width: '100%',
        height: 140,
        backgroundColor: '#f8fafc',
    },
    marketCardInfo: {
        padding: 12,
    },
    marketCardName: {
        fontSize: 15,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 4,
    },
    marketCardDesc: {
        fontSize: 11,
        color: '#64748b',
        lineHeight: 15,
        marginBottom: 10,
        height: 30,
    },
    marketCardFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    marketCardPrice: {
        fontSize: 14,
        fontWeight: '800',
        color: '#1e293b',
    },
    marketCardAddBtn: {
        backgroundColor: '#1e293b',
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 10,
        paddingVertical: 6,
        borderRadius: 8,
        gap: 3,
    },
    marketCardAddText: {
        color: 'white',
        fontSize: 10,
        fontWeight: 'bold',
    },
    emptyStateContainerCentered: {
        width: '100%',
        alignItems: 'center',
        paddingVertical: 80,
        gap: 15,
    },

    // Cart Modal Styles
    cartModalContent: {
        backgroundColor: 'white',
        borderTopLeftRadius: 32,
        borderTopRightRadius: 32,
        height: '80%',
        width: '100%',
        paddingTop: 20,
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: 24,
        marginBottom: 20,
    },
    modalTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    cartItemsList: {
        flex: 1,
        paddingHorizontal: 24,
    },
    cartItemCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f8fafc',
        borderRadius: 16,
        padding: 12,
        marginBottom: 12,
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    cartItemImg: {
        width: 60,
        height: 60,
        borderRadius: 12,
    },
    cartItemInfo: {
        flex: 1,
        marginLeft: 15,
    },
    cartItemName: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    cartItemPriceRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: 5,
        paddingRight: 10,
    },
    cartItemPrice: {
        fontSize: 13,
        fontWeight: '700',
        color: '#3b82f6',
    },
    cartItemQty: {
        fontSize: 11,
        color: '#64748b',
    },
    cartFooter: {
        padding: 24,
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
        backgroundColor: 'white',
    },
    cartTotalRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 20,
    },
    cartTotalLabel: {
        fontSize: 15,
        color: '#64748b',
    },
    cartTotalValue: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    checkoutBtn: {
        backgroundColor: '#1e293b',
        paddingVertical: 16,
        borderRadius: 16,
        alignItems: 'center',
    },
    checkoutBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 16,
    },
    emptyCartCentered: {
        alignItems: 'center',
        paddingVertical: 100,
        gap: 15,
    },
    emptyCartText: {
        color: '#94a3b8',
        fontSize: 16,
    },

    // Checkout Modal Styles
    checkoutModalContent: {
        backgroundColor: 'white',
        borderTopLeftRadius: 32,
        borderTopRightRadius: 32,
        height: '85%',
        width: '100%',
        paddingTop: 20,
    },
    checkoutFormScroll: {
        paddingHorizontal: 24,
    },
    formLabel: {
        fontSize: 13,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 8,
        marginTop: 15,
    },
    nextBtn: {
        backgroundColor: '#1e293b',
        paddingVertical: 16,
        borderRadius: 16,
        alignItems: 'center',
        marginTop: 30,
        marginBottom: 50,
    },
    nextBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 16,
    },
    paymentContainer: {
        flex: 1,
        paddingHorizontal: 24,
        alignItems: 'center',
        paddingTop: 40,
    },
    paymentSummary: {
        width: '100%',
        backgroundColor: '#f8fafc',
        padding: 30,
        borderRadius: 24,
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#e2e8f0',
        marginBottom: 20,
    },
    paymentTotalLabel: {
        fontSize: 14,
        color: '#64748b',
        marginBottom: 5,
    },
    paymentTotalValue: {
        fontSize: 32,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    paymentDesc: {
        textAlign: 'center',
        color: '#64748b',
        lineHeight: 20,
        marginBottom: 40,
    },
    payNowBtn: {
        backgroundColor: '#10b981',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 18,
        width: '100%',
        borderRadius: 16,
        gap: 10,
    },
    payNowText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 16,
    },
    statusBadgeTextSmall_Adopted: {
        color: '#1e40af',
    },
    statusBadgeTextSmall_Pending: {
        color: '#92400e',
    },
    statusBadgeTextSmall_Active: {
        color: '#166534',
    },

    // Order Success Styles
    successContainer: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 40,
    },
    checkmarkCircle: {
        width: 100,
        height: 100,
        borderRadius: 50,
        backgroundColor: '#10b981',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 30,
    },
    successTitle: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 10,
    },
    successDesc: {
        textAlign: 'center',
        color: '#64748b',
        marginBottom: 40,
    },
    doneBtn: {
        backgroundColor: '#1e293b',
        paddingHorizontal: 30,
        paddingVertical: 15,
        borderRadius: 12,
    },
    doneBtnText: {
        color: 'white',
        fontWeight: 'bold',
    },

    // Order List Styles
    orderCard: {
        backgroundColor: 'white',
        borderRadius: 16,
        padding: 15,
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    orderHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
        paddingBottom: 12,
        marginBottom: 12,
    },
    orderIdText: {
        fontWeight: 'bold',
        fontSize: 15,
        color: '#1e293b',
    },
    orderDateText: {
        fontSize: 12,
        color: '#94a3b8',
    },
    orderFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    orderTotalLabel: {
        fontSize: 13,
        color: '#64748b',
    },
    orderTotalValue: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
    },

    // Razorpay-style styles
    razorpayContainerFull: {
        flex: 1,
        backgroundColor: 'white',
        overflow: 'hidden',
    },
    razorpaySplash: {
        flex: 1,
        backgroundColor: 'white',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 40,
    },
    blueShield: {
        width: 180,
        height: 180,
        borderRadius: 40,
        backgroundColor: '#3399cc',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 30,
        elevation: 10,
        shadowColor: '#3399cc',
        shadowOpacity: 0.3,
        shadowOffset: { width: 0, height: 10 },
    },
    securedByText: {
        fontSize: 14,
        color: '#64748b',
        letterSpacing: 0.5,
    },
    razorpayContact: {
        flex: 1,
        backgroundColor: 'white',
        padding: 24,
        alignItems: 'center',
        justifyContent: 'center',
    },
    contactTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 8,
    },
    contactHeaderLine: {
        width: 40,
        height: 3,
        backgroundColor: '#10b981',
        borderRadius: 2,
        marginBottom: 25,
    },
    contactSub: {
        fontSize: 14,
        color: '#64748b',
        textAlign: 'center',
        marginBottom: 30,
    },
    phoneInputRow: {
        flexDirection: 'row',
        width: '100%',
        height: 55,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        borderRadius: 12,
        overflow: 'hidden',
    },
    flagBox: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        backgroundColor: '#f8fafc',
        borderRightWidth: 1,
        borderRightColor: '#e2e8f0',
        gap: 5,
    },
    razorpayPhoneInput: {
        flex: 1,
        paddingHorizontal: 15,
        fontSize: 16,
        color: '#1e293b',
    },
    razorpayContinueBtn: {
        backgroundColor: '#002e25',
        width: '100%',
        height: 55,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 30,
        marginBottom: 15,
    },
    razorpayContinueText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 16,
    },
    securedByTiny: {
        fontSize: 11,
        color: '#94a3b8',
    },
    razorpayMethods: {
        flex: 1,
        flexDirection: 'row',
    },
    razorpaySidebar: {
        width: '40%',
        backgroundColor: '#10b981',
        padding: 20,
        justifyContent: 'space-between',
    },
    sidebarBrand: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
    },
    razorpayLogo: {
        width: 30,
        height: 30,
        borderRadius: 6,
        backgroundColor: 'white',
    },
    razorpayBrandName: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 14,
    },
    priceSummaryBox: {
        backgroundColor: 'rgba(255,255,255,0.1)',
        padding: 15,
        borderRadius: 12,
        marginTop: 20,
    },
    priceSummaryLabel: {
        color: 'white',
        fontSize: 11,
        opacity: 0.8,
        marginBottom: 4,
    },
    priceSummaryValue: {
        color: 'white',
        fontSize: 20,
        fontWeight: 'bold',
    },
    userSummaryBox: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 20,
        paddingVertical: 10,
        borderTopWidth: 1,
        borderTopColor: 'rgba(255,255,255,0.1)',
    },
    userSummaryLabel: {
        color: 'white',
        fontSize: 10,
        opacity: 0.7,
    },
    userSummaryValue: {
        color: 'white',
        fontSize: 12,
        fontWeight: '600',
    },
    razorpayFooter: {
        marginTop: 'auto',
    },
    securedByTextWhite: {
        color: 'white',
        fontSize: 10,
        opacity: 0.6,
    },
    methodsContent: {
        width: '60%',
        backgroundColor: 'white',
        padding: 20,
    },
    methodsHeader: {
        marginBottom: 15,
    },
    methodsTitle: {
        fontSize: 13,
        fontWeight: 'bold',
        color: '#64748b',
        textTransform: 'uppercase',
        letterSpacing: 0.5,
    },
    methodsList: {
        flex: 1,
    },
    methodGroup: {
        marginBottom: 10,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        borderRadius: 12,
        overflow: 'hidden',
    },
    methodItem: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: 15,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    methodItemActive: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: 15,
        backgroundColor: '#f8fafc',
    },
    methodName: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    methodSub: {
        fontSize: 11,
        color: '#94a3b8',
        marginTop: 2,
    },
    cardForm: {
        padding: 15,
        backgroundColor: 'white',
        gap: 12,
    },
    formLabelSmall: {
        fontSize: 12,
        color: '#64748b',
        marginBottom: 5,
    },
    razorpayInput: {
        height: 45,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        borderRadius: 8,
        paddingHorizontal: 12,
        fontSize: 14,
        color: '#1e293b',
    },
    checkboxRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        marginTop: 5,
    },
    checkboxText: {
        fontSize: 11,
        color: '#64748b',
    },
    razorpayPayBtn: {
        backgroundColor: '#002e25',
        height: 50,
        borderRadius: 10,
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 15,
    },
    razorpayPayBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 15,
    },
    bankGrid: {
        padding: 15,
        backgroundColor: 'white',
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
    },
    bankRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 15,
        gap: 10,
    },
    bankIconBtn: {
        flex: 1,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        borderRadius: 8,
        padding: 10,
        alignItems: 'center',
        justifyContent: 'center',
    },
    bankShortName: {
        fontSize: 12,
        fontWeight: 'bold',
        color: '#1e293b',
        marginBottom: 2,
    },
    bankFullName: {
        fontSize: 9,
        color: '#64748b',
    },
    otherBanksBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 10,
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
        gap: 8,
    },
    otherBanksText: {
        fontSize: 12,
        color: '#64748b',
        fontWeight: '600',
    },
    bankIconBtnActive: {
        borderColor: '#10b981',
        backgroundColor: '#f0fdf4',
        borderWidth: 2,
    },
    bankTextActive: {
        color: '#10b981',
    },
    // Found Pet Reports Styles
    reportCard: {
        backgroundColor: '#ffffff',
        borderRadius: 20,
        padding: 20,
        marginBottom: 15,
        borderWidth: 1,
        borderColor: '#f1f5f9',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    reportHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 15,
        marginBottom: 15,
    },
    reportPetImg: {
        width: 60,
        height: 60,
        borderRadius: 15,
        backgroundColor: '#f8fafc',
    },
    reportTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#1e293b',
        fontFamily: Platform.OS === 'ios' ? 'Outfit' : 'sans-serif-medium',
    },
    reportDate: {
        fontSize: 12,
        color: '#94a3b8',
    },
    reporterBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        backgroundColor: '#f1f5f9',
        paddingVertical: 8,
        paddingHorizontal: 12,
        borderRadius: 10,
        marginBottom: 12,
    },
    reporterText: {
        fontSize: 13,
        color: '#475569',
        fontWeight: '600',
    },
    locationRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        marginBottom: 12,
    },
    locationText: {
        fontSize: 14,
        color: '#1e293b',
        fontWeight: '500',
    },
    notesBox: {
        backgroundColor: '#fffbeb',
        borderLeftWidth: 4,
        borderLeftColor: '#f59e0b',
        padding: 12,
        borderRadius: 8,
        marginBottom: 15,
    },
    notesText: {
        fontSize: 14,
        color: '#92400e',
        fontStyle: 'italic',
    },
    safeBtn: {
        backgroundColor: '#3b82f6',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 12,
        borderRadius: 12,
        gap: 8,
    },
    safeBtnText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 15,
    },
    emptyContainerFull: {
        alignItems: 'center',
        paddingVertical: 40,
        paddingHorizontal: 20,
        backgroundColor: '#ffffff',
        borderRadius: 30,
        borderWidth: 2,
        borderColor: '#f1f5f9',
        borderStyle: 'dashed',
    },
    mailboxIconContainer: {
        width: 140,
        height: 140,
        borderRadius: 70,
        backgroundColor: '#f8fafc',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 25,
    },
    emptyTitleLarge: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#1e293b',
        textAlign: 'center',
        marginBottom: 12,
        fontFamily: Platform.OS === 'ios' ? 'Outfit' : 'sans-serif-medium',
    },
    emptyDescLarge: {
        fontSize: 14,
        color: '#64748b',
        textAlign: 'center',
        lineHeight: 22,
        marginBottom: 30,
        paddingHorizontal: 10,
    },
    emptyActionRow: {
        flexDirection: 'row',
        gap: 12,
    },
    emptyBtnPrimary: {
        backgroundColor: '#3b82f6',
        paddingVertical: 12,
        paddingHorizontal: 20,
        borderRadius: 12,
    },
    emptyBtnTextPrimary: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 14,
    },
    emptyBtnSecondary: {
        backgroundColor: '#f1f5f9',
        paddingVertical: 12,
        paddingHorizontal: 20,
        borderRadius: 12,
    },
    emptyBtnTextSecondary: {
        color: '#475569',
        fontWeight: 'bold',
        fontSize: 14,
    },
    // Bank Simulation Styles
    bankProcessingScreen: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: '#fff',
        zIndex: 100,
    },
    bankHeaderMock: {
        backgroundColor: '#f8fafc',
        padding: 20,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#e2e8f0',
    },
    bankHeaderText: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#1e293b',
    },
    bankContentMock: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        padding: 30,
    },
    bankLogoPlaceholder: {
        width: 100,
        height: 100,
        backgroundColor: '#3b82f6',
        borderRadius: 50,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 20,
    },
    bankLogoText: {
        color: '#fff',
        fontSize: 24,
        fontWeight: 'bold',
    },
    bankProcessingText: {
        fontSize: 16,
        color: '#64748b',
        textAlign: 'center',
        lineHeight: 24,
    },
    simulatePayBtn: {
        backgroundColor: '#10b981',
        paddingVertical: 15,
        paddingHorizontal: 30,
        borderRadius: 12,
        marginTop: 20,
    },
    simulatePayText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
    premiumBannerLost: {
        marginHorizontal: 15,
        marginTop: 15,
        borderRadius: 15,
        overflow: 'hidden',
        elevation: 5,
        shadowColor: '#ef4444',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
    },
    bannerGradient: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: 15,
    },
    bannerLeftScale: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 12,
    },
    bannerIconWhite: {
        width: 44,
        height: 44,
        backgroundColor: 'white',
        borderRadius: 22,
        alignItems: 'center',
        justifyContent: 'center',
    },
    bannerTitleWhite: {
        color: 'white',
        fontSize: 16,
        fontWeight: '900',
        letterSpacing: 1,
    },
    bannerSubtitleWhite: {
        color: 'rgba(255,255,255,0.9)',
        fontSize: 12,
        fontWeight: '600',
    },
});
