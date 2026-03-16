from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import time

# ── Config ────────────────────────────────────────────────────────────────────
URL   = "http://localhost/PetCloud"
EMAIL = "joshuajoseph10310@gmail.com"
PASS  = "admin"

# ── Driver factory ───────────────────────────────────────────────────────────
def get_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    return webdriver.Chrome(
        service=Service(ChromeDriverManager().install()), options=options
    )

# ── Robust admin login ────────────────────────────────────────────────────────
def admin_login(driver):
    print(f"  Logging in as {EMAIL} ...")
    driver.get(f"{URL}/index.php")

    # Wait for login form
    WebDriverWait(driver, 15).until(
        EC.presence_of_element_located((By.NAME, "email"))
    )
    driver.find_element(By.NAME, "email").clear()
    driver.find_element(By.NAME, "email").send_keys(EMAIL)
    driver.find_element(By.NAME, "password").clear()
    driver.find_element(By.NAME, "password").send_keys(PASS)
    driver.find_element(By.NAME, "password").submit()

    # Poll for up to 15s for URL to leave index.php
    for _ in range(30):
        time.sleep(0.5)
        cur = driver.current_url
        if "index.php" not in cur and "PetCloud" in cur:
            break

    final_url = driver.current_url
    print(f"  After login URL: {final_url}")

    # If still on login, try direct nav (session cookie may be set)
    if "index.php" in final_url or final_url.endswith("/PetCloud/") or final_url.endswith("/PetCloud"):
        driver.get(f"{URL}/admin-dashboard.php")
        time.sleep(2)
        final_url = driver.current_url
        print(f"  After direct nav: {final_url}")

    if "admin-dashboard" in final_url or "dashboard" in final_url:
        print("  SUCCESS: Admin logged in")
        return True

    raise Exception(f"Login failed. URL stuck at: {final_url}")

# ── TEST 1: Admin Login ───────────────────────────────────────────────────────
def test_admin_login():
    print("\n--- TEST 1: Admin Login ---")
    driver = get_driver()
    try:
        admin_login(driver)
        print("PASS: Admin login successful")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 2: Admin Dashboard ───────────────────────────────────────────────────
def test_admin_dashboard():
    print("\n--- TEST 2: Admin Dashboard Stats ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/admin-dashboard.php")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(2)
        print(f"  Page title: {driver.title}")
        src = driver.page_source.lower()
        assert any(k in src for k in ["dashboard", "admin", "users", "stat", "total"])
        print("PASS: Admin dashboard loaded")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 3: Admin Users Page ──────────────────────────────────────────────────
def test_admin_users_page():
    print("\n--- TEST 3: Admin Users Page ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/admin-users.php")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(2)
        rows = driver.find_elements(By.CSS_SELECTOR, "table tbody tr")
        print(f"  Rows found: {len(rows)} | Title: {driver.title}")
        assert "404" not in driver.title.lower()
        print("PASS: Admin users page loaded")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 4: Admin Shop Approvals ──────────────────────────────────────────────
def test_admin_shop_approvals():
    print("\n--- TEST 4: Admin Shop Approvals Page ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/admin-shop-approvals.php")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(2)
        print(f"  URL: {driver.current_url} | Title: {driver.title}")
        assert "404" not in driver.title.lower()
        print("PASS: Admin shop approvals page loaded")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 5: Admin Adoption Approvals ─────────────────────────────────────────
def test_admin_adoption_approvals():
    print("\n--- TEST 5: Admin Adoption Approvals Page ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/admin-adoption-approvals.php")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(2)
        print(f"  URL: {driver.current_url} | Title: {driver.title}")
        assert "404" not in driver.title.lower()
        print("PASS: Admin adoption approvals page loaded")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 6: Admin Orders ──────────────────────────────────────────────────────
def test_admin_orders():
    print("\n--- TEST 6: Admin Platform Orders Page ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/admin-platform-orders.php")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(2)
        print(f"  URL: {driver.current_url} | Title: {driver.title}")
        assert "404" not in driver.title.lower()
        print("PASS: Admin platform orders page loaded")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 7: Admin Logs ────────────────────────────────────────────────────────
def test_admin_logs():
    print("\n--- TEST 7: Admin Activity Logs Page ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/admin-logs.php")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(2)
        print(f"  URL: {driver.current_url} | Title: {driver.title}")
        assert "404" not in driver.title.lower()
        print("PASS: Admin logs page loaded")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── TEST 8: Admin Logout ──────────────────────────────────────────────────────
def test_admin_logout():
    print("\n--- TEST 8: Admin Logout ---")
    driver = get_driver()
    try:
        admin_login(driver)
        driver.get(f"{URL}/logout.php")
        for _ in range(20):
            time.sleep(0.5)
            if "index.php" in driver.current_url or driver.current_url.endswith("/PetCloud/"):
                break
        print(f"  After logout URL: {driver.current_url}")
        assert "admin-dashboard" not in driver.current_url
        print("PASS: Admin logged out successfully")
    except Exception as e:
        print(f"FAIL: {e}")
    finally:
        time.sleep(1)
        driver.quit()

# ── Runner ────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("=" * 55)
    print("  PetCloud Admin Panel - Selenium Test Suite")
    print(f"  URL : {URL}")
    print(f"  User: {EMAIL}")
    print("=" * 55)

    test_admin_login()
    test_admin_dashboard()
    test_admin_users_page()
    test_admin_shop_approvals()
    test_admin_adoption_approvals()
    test_admin_orders()
    test_admin_logs()
    test_admin_logout()

    print("\n" + "=" * 55)
    print("  All admin tests completed.")
    print("=" * 55)
