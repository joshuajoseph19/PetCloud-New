# PetCloud Service Types Expansion Plan

## 1. Overview
This document outlines the strategy to expand PetCloud's service offerings from the basic three (Checkup, Grooming, Vaccination) to a comprehensive, scalable system supporting medical, wellness, grooming, and specialized care.

## 2. Expanded Service Categories & List

We will organize services into **12 core categories** to maintain a clean UI while offering depth.

### 🏥 1. Medical Consultation
*Core veterinary services.*
- General Checkup
- Emergency Consultation
- Video Consultation
- Specialist Consultation (Dermatology, Orthopedics, etc.)
- Follow-up Visit

### 🛡️ 2. Preventive & Wellness
*Routine care to keep pets healthy.*
- Vaccination
- Deworming
- Microchipping
- Tick & Flea Treatment
- Diet & Nutrition Counseling

### 🛁 3. Grooming & Spa
*Hygiene and aesthetic services.*
- Basic Bath & Blow Dry
- Full Grooming Package (Cut, Bath, Style)
- Nail Clipping
- Ear Cleaning
- Sanitary Trim
- Medicated Bath
- De-shedding Treatment

### 🔬 4. Diagnostics & Lab
*Testing and imaging.*
- Blood Test (CBC / Biochemistry)
- X-Ray / Radiography
- Ultrasound
- Urine / Stool Analysis
- Allergy Testing

### 💉 5. Surgery & Dental
*Procedures requiring anesthesia or sedation.*
- Spay / Neuter Surgery
- Dental Scaling & Polishing
- Tooth Extraction
- Wound Dressing / Suturing
- Minor Surgical Procedures

### 🧘 6. Alternative Therapy
*Rehabilitation and holistic care.*
- Physiotherapy
- Acupuncture
- Laser Therapy
- Hydrotherapy

### 🎓 7. Training & Behavior
*Mental and behavioral health.*
- Obedience Training
- Puppy Socialization
- Behavioral Modification
- Agility Training

### 🏠 8. Boarding & Daycare
*Care when owners are away.*
- Daycare (Full Day)
- Overnight Boarding
- Pet Sitting (Hourly)

### 🚕 9. Pet Transportation
*Logistics and travel solutions.*
- Pet Taxi (Local)
- Pet Ambulance (Emergency)
- Airport Transfer
- Long Distance Transport

### 🦮 10. Walking & Sitting
*Daily exercise and home care.*
- Dog Walking (30 min)
- Dog Walking (60 min)
- Home Check-in

### 📸 11. Pet Photography
*Capturing memories.*
- Studio Portrait
- Outdoor Session
- Event Photography

### ⚖️ 12. Insurance & Legal
*Administrative and protection services.*
- Insurance Consultation
- Travel Documentation
- License Registration

---

## 3. Database Structure

The database is designed for flexibility. It separates the "Global" definition of a service from the "Local" implementation at a specific clinic.

### Key Tables
1.  **`service_categories`**: Static list of the ~8 categories above.
2.  **`services`**: The master list of all 40+ services.
    *   Contains `default_duration` and `is_medical` flags.
3.  **`service_pet_type_compatibility`**: (Pivot) Controls visibility.
    *   *Example:* "Beak Trimming" only links to "Bird".
4.  **`clinic_services`**: (Pivot) The most important table.
    *   Links `clinic_id` + `service_id`.
    *   **Overrides:** `price`, `duration`, `is_available`.
    *   Allows Clinic A to charge $50 for a Groom while Clinic B charges $70.

---

## 4. UI/UX Flow

### Step 1: Pet Selection 🐾
*   **User Action:** Selects "Fluffy (Cat)".
*   **System Logic:** Filters subsequent service lists to only show Cat-compatible services.

### Step 2: Category Grid 📱
*   **Display:** A grid of 8 cards with icons (Medical, Grooming, etc.).
*   **User Action:** Clicks "Grooming".

### Step 3: Service Selection ✅
*   **Display:** List of grooming services relevant to Cats.
*   **Visuals:** Each service shows a name, base price range (or "Starts at..."), and estimated duration.
*   **User Action:** Selects "Full Grooming".

### Step 4: Clinic/Provider Selection 🏥
*   **Display:** List of clinics offering "Full Grooming" for Cats near the user.
*   **Details:** Shows specific price at that clinic (e.g., "₹1,500") and next available slot.
*   **User Action:** Selects "City Vet Clinic".

### Step 5: Booking & Payment 📅
*   **Display:** Calendar with specific time slots based on the clinic's `duration` setting for that service.
*   **User Action:** Confirms slot and pays.

---

## 5. Development Best Practices

### Filtering Relevant Services
*   **Never show all services at once.** Always filter by the selected Pet Type ID.
*   Use the `service_pet_type_compatibility` table. If a user selects a "Fish", do not show "Grooming -> Haircut".

### Handling Expansion
*   **Database-Driven UI:** Do not hardcode service IDs in the frontend (e.g., `if (id === 1) ...`).
*   Render the list directly from the API response calling `get_services_by_category()`.
*   This allows you to add "Pet Massage" to the database later, and it will automatically appear in the app without an update.

### Clinic Availability (The "Uber" Model)
*   A service active in the master `services` table does **not** mean it appears for every clinic.
*   Clinics must "opt-in" by creating a record in `clinic_services`.
*   If no clinics in the search radius offer "Acupuncture", show a helpful "No providers nearby" message rather than hiding the service entirely (which users find confusing).

### Deprecation Strategy
*   If you remove a service, use `is_active = 0` (Soft Delete).
*   Never `DELETE` from the database, or old booking records will break.

---

## 6. Implementation Checklist

1.  [ ] **Run SQL Script:** Execute `database/services_schema_expansion.sql`.
2.  [ ] **Seed Data:** Verify the 40+ services are populated.
3.  [ ] **Backend API:** Create endpoints:
    *   `GET /api/categories`
    *   `GET /api/services?category_id=X&pet_type_id=Y`
    *   `GET /api/clinics?service_id=Z&lat=...&long=...`
4.  [ ] **Frontend Update:** Replace the current static "3 service buttons" with a dynamic grid fetching from `/api/categories`.
5.  [ ] **Admin Panel:** Add a page for Clinics to "Manage My Services" (toggle availability and set prices).
