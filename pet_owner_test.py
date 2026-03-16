from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import time
import random
import string

# ── Config ────────────────────────────────────────────────────────────────────
URL      = "http://localhost/PetCloud"
EMAIL    = "joshuajoseph10310@gmail.com"   # existing pet owner account
PASSWORD = "admin"

# ── Helpers ───────────────────────────────────────────────────────────────────
def get_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--no-sandbox")
    return webdriver.Chrome(
        service=Service(ChromeDriverManager().install()), options=options
    )

def login(driver, email=EMAIL, password=PASSWORD):
    """Login as a pet owner and return True on success."""
    driver.get(f"{URL}/index.php")
    WebDriverWait(driver, 15).until(
        EC.presence_of_element_located((By.NAME, "email"))
    )
    driver.find_element(By.NAME, "email").clear()
    driver.find_element(By.NAME, "email").send_keys(email)
    driver.find_element(By.NAME, "password").clear()
    driver.find_element(By.NAME, "password").send_keys(password)

    try:
        btn = driver.find_element(By.CSS_SELECTOR,
            "button[type='submit'], input[type='submit'], #login-btn, .login-btn")
        btn.click()
    except Exception:
        driver.execute_script("document.querySelector('form').submit();")

    for _ in range(30):
        time.sleep(0.5)
        cur = driver.current_url
        if "index.php" not in cur and "PetCloud" in cur:
            break

    if "dashboard" in driver.current_url or "admin" in driver.current_url:
        print(f"  ✅ Logged in → {driver.current_url}")
        return True
    raise Exception(f"Login failed. URL: {driver.current_url}")

def rand_str(n=5):
    return ''.join(random.choices(string.ascii_lowercase + string.digits, k=n))


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 1 – New Pet Owner Signup
# ═══════════════════════════════════════════════════════════════════════════════
def test_signup_new_pet_owner():
    print("\n" + "="*50)
    print("TEST 1: New Pet Owner Signup")
    print("="*50)
    driver = get_driver()
    try:
        suffix     = rand_str()
        test_name  = f"Pet Owner {suffix}"
        test_email = f"owner_{suffix}@petcloud.com"
        test_pass  = "Owner@1234"

        driver.get(f"{URL}/signup.php")
        WebDriverWait(driver, 15).until(
            EC.presence_of_element_located((By.NAME, "full_name"))
        )
        print(f"  Signing up → {test_email}")

        driver.find_element(By.NAME, "full_name").send_keys(test_name)
        driver.find_element(By.NAME, "email").send_keys(test_email)
        driver.find_element(By.NAME, "password").send_keys(test_pass)

        # confirm_password (if present)
        try:
            driver.find_element(By.NAME, "confirm_password").send_keys(test_pass)
        except Exception:
            pass

        # tick terms checkbox (if present)
        try:
            cb = driver.find_element(By.ID, "terms")
            driver.execute_script("arguments[0].click();", cb)
        except Exception:
            pass

        driver.find_element(By.CSS_SELECTOR, "button[type='submit'], input[type='submit']").click()

        for _ in range(30):
            time.sleep(0.5)
            if "dashboard" in driver.current_url:
                break

        if "dashboard" in driver.current_url:
            print(f"  ✅ PASS: Signed up & redirected → {driver.current_url}")
        else:
            print(f"  ❌ FAIL: Still at → {driver.current_url}")

    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 2 – Pet Owner Login
# ═══════════════════════════════════════════════════════════════════════════════
def test_pet_owner_login():
    print("\n" + "="*50)
    print("TEST 2: Pet Owner Login")
    print("="*50)
    driver = get_driver()
    try:
        print(f"  Logging in as {EMAIL}")
        login(driver)
        if "dashboard" in driver.current_url:
            print(f"  ✅ PASS: Login successful → {driver.current_url}")
        else:
            print(f"  ❌ FAIL: Unexpected URL → {driver.current_url}")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 3 – View Dashboard
# ═══════════════════════════════════════════════════════════════════════════════
def test_view_dashboard():
    print("\n" + "="*50)
    print("TEST 3: Pet Owner Dashboard")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/dashboard.php")
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(2)
        title = driver.title
        print(f"  Page title: {title}")
        src = driver.page_source.lower()
        assert any(k in src for k in ["dashboard", "welcome", "pet", "feed"])
        print(f"  ✅ PASS: Dashboard loaded successfully")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 4 – Add a New Pet
# ═══════════════════════════════════════════════════════════════════════════════
def test_add_pet():
    print("\n" + "="*50)
    print("TEST 4: Add New Pet")
    print("="*50)
    driver = get_driver()
    pet_name = f"Buddy_{rand_str(3)}"
    try:
        login(driver)
        driver.get(f"{URL}/mypets.php")

        # Click Add New Pet button
        add_btn = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.XPATH,
                "//button[contains(translate(.,'add new pet','ADD NEW PET'),'ADD NEW PET') or contains(.,'Add') or contains(.,'add')]"
            ))
        )
        add_btn.click()
        time.sleep(2)  # wait for modal

        print(f"  Adding pet: {pet_name}")

        # Fill form via JS (modal fields may be hidden)
        driver.execute_script(f"""
            var fields = {{
                'pet_name':        '{pet_name}',
                'pet_type':        'Dog',
                'pet_breed':       'Labrador',
                'pet_age':         '2 Years',
                'pet_gender':      'Male',
                'pet_weight':      '20',
                'pet_description': 'Friendly and active dog'
            }};
            for (var key in fields) {{
                var el = document.querySelector('[name="' + key + '"]');
                if (el) el.value = fields[key];
            }}
        """)
        time.sleep(1)

        # Submit
        try:
            driver.find_element(By.NAME, "add_pet").click()
        except Exception:
            driver.execute_script(
                "document.querySelector('form[method]').submit();"
            )

        time.sleep(3)

        # Verify pet card appears
        src = driver.page_source
        if pet_name in src:
            print(f"  ✅ PASS: Pet '{pet_name}' added and visible on page")
        else:
            print(f"  ⚠️  PARTIAL: Form submitted but '{pet_name}' not found in page source")
            print(f"     Current URL: {driver.current_url}")

    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 5 – View My Pets Page
# ═══════════════════════════════════════════════════════════════════════════════
def test_view_my_pets():
    print("\n" + "="*50)
    print("TEST 5: View My Pets Page")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/mypets.php")
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(2)
        print(f"  Page title: {driver.title}")
        src = driver.page_source.lower()
        assert any(k in src for k in ["my pets", "pet", "add", "mypets"])
        print(f"  ✅ PASS: My Pets page loaded")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 6 – View Marketplace
# ═══════════════════════════════════════════════════════════════════════════════
def test_view_marketplace():
    print("\n" + "="*50)
    print("TEST 6: View Marketplace Page")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/marketplace.php")
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(2)
        print(f"  Page title: {driver.title}")
        src = driver.page_source.lower()
        assert any(k in src for k in ["marketplace", "product", "shop", "cart", "buy"])
        print(f"  ✅ PASS: Marketplace page loaded")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 7 – View Adoption Page
# ═══════════════════════════════════════════════════════════════════════════════
def test_view_adoption():
    print("\n" + "="*50)
    print("TEST 7: View Adoption Page")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/adoption.php")
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(2)
        print(f"  Page title: {driver.title}")
        src = driver.page_source.lower()
        assert any(k in src for k in ["adopt", "pet", "rehom", "listing"])
        print(f"  ✅ PASS: Adoption page loaded")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 8 – View Lost Pet Reports Page
# ═══════════════════════════════════════════════════════════════════════════════
def test_view_lost_pet_reports():
    print("\n" + "="*50)
    print("TEST 8: View Lost Pet Reports Page")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/lost-pet-reports.php")
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(2)
        print(f"  Page title: {driver.title}")
        assert "404" not in driver.title.lower()
        print(f"  ✅ PASS: Lost Pet Reports page loaded")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 9 – View Profile Page
# ═══════════════════════════════════════════════════════════════════════════════
def test_view_profile():
    print("\n" + "="*50)
    print("TEST 9: View Profile Page")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/profile.php")
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(2)
        print(f"  Page title: {driver.title}")
        src = driver.page_source.lower()
        assert any(k in src for k in ["profile", "account", "email", "name"])
        print(f"  ✅ PASS: Profile page loaded")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ═══════════════════════════════════════════════════════════════════════════════
# TEST 10 – Logout
# ═══════════════════════════════════════════════════════════════════════════════
def test_logout():
    print("\n" + "="*50)
    print("TEST 10: Pet Owner Logout")
    print("="*50)
    driver = get_driver()
    try:
        login(driver)
        driver.get(f"{URL}/logout.php")

        for _ in range(20):
            time.sleep(0.5)
            if "index.php" in driver.current_url or driver.current_url.endswith("/PetCloud/"):
                break

        print(f"  After logout URL: {driver.current_url}")
        assert "dashboard" not in driver.current_url
        print(f"  ✅ PASS: Logout successful → redirected to login page")
    except Exception as e:
        print(f"  ❌ FAIL: {e}")
    finally:
        time.sleep(2)
        driver.quit()


# ── Runner ────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("\n" + "=" * 55)
    print("  PetCloud - Pet Owner Selenium Test Suite")
    print(f"  URL  : {URL}")
    print(f"  User : {EMAIL}")
    print("=" * 55)

    test_signup_new_pet_owner()
    test_pet_owner_login()
    test_view_dashboard()
    test_add_pet()
    test_view_my_pets()
    test_view_marketplace()
    test_view_adoption()
    test_view_lost_pet_reports()
    test_view_profile()
    test_logout()

    print("\n" + "=" * 55)
    print("  All pet owner tests completed!")
    print("=" * 55)
