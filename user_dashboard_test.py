from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import time

# ── Configuration ────────────────────────────────────────────────────────────
URL      = "http://localhost/PetCloud"
EMAIL    = "joshuajoseph10310@gmail.com" 
PASSWORD = "admin"

def get_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    # options.add_argument("--headless") # Headless mode for clean run
    return webdriver.Chrome(
        service=Service(ChromeDriverManager().install()), options=options
    )

def login_and_goto_dashboard(driver):
    print(f"  Logging in as {EMAIL}...")
    driver.get(f"{URL}/index.php")
    
    WebDriverWait(driver, 15).until(
        EC.presence_of_element_located((By.NAME, "email"))
    )
    
    driver.find_element(By.NAME, "email").send_keys(EMAIL)
    driver.find_element(By.NAME, "password").send_keys(PASSWORD)
    
    try:
        btn = driver.find_element(By.CSS_SELECTOR, "button[type='submit'], #login-btn")
        btn.click()
    except:
        driver.find_element(By.NAME, "password").submit()

    # Wait for dashboard to load (redirect might take a second)
    WebDriverWait(driver, 15).until(
        lambda d: "dashboard.php" in d.current_url or "admin-dashboard.php" in d.current_url
    )
    print(f"  Reached dashboard: {driver.current_url.split('/')[-1]}")

# ── TEST 1: Hero Section & Greeting ──────────────────────────────────────────
def test_hero_and_greeting():
    print("\n--- TEST 1: Hero Section & Greeting ---")
    driver = get_driver()
    try:
        login_and_goto_dashboard(driver)
        
        # Check URL
        assert "dashboard" in driver.current_url
        
        # Check Greeting
        body_text = driver.find_element(By.TAG_NAME, "body").text
        greetings = ["Good Morning", "Good Afternoon", "Good Evening", "Good Night"]
        found = any(g in body_text for g in greetings)
        if found:
            print("  [PASS] Found time-based greeting.")
        else:
            print("  [FAIL] Could not find greeting message.")

        # Check for User Name
        if "joshua" in body_text.lower() or "pet lover" in body_text.lower():
            print("  [PASS] User name or default greeting found.")
            
    except Exception as e:
        print(f"  [ERROR] {e}")
    finally:
        driver.quit()

# ── TEST 2: Sidebar & Navigation ─────────────────────────────────────────────
def test_sidebar_navigation():
    print("\n--- TEST 2: Sidebar & Navigation ---")
    driver = get_driver()
    try:
        login_and_goto_dashboard(driver)
        
        # Check for presence of sidebar or navigation links
        nav_items = ["My Pets", "Marketplace", "Adoption", "Smart Feeder"]
        for item in nav_items:
            try:
                # Look for link containing the text
                link = WebDriverWait(driver, 5).until(
                    EC.presence_of_element_located((By.PARTIAL_LINK_TEXT, item))
                )
                print(f"  [PASS] Nav link for '{item}' confirmed.")
            except:
                print(f"  [WARN] Nav link for '{item}' not found visually.")

    except Exception as e:
        print(f"  [ERROR] {e}")
    finally:
        driver.quit()

# ── TEST 3: Dashboard Modules ────────────────────────────────────────────────
def test_dashboard_modules():
    print("\n--- TEST 3: Dashboard Modules ---")
    driver = get_driver()
    try:
        login_and_goto_dashboard(driver)
        
        # Wait for page elements
        time.sleep(2)
        
        # Looking for specific headers in dashboard.php
        modules = {
            "Health Reminders": "health",
            "Feeding Schedule": "feed",
            "Daily Tasks": "task",
            "Quick Stats": "stat"
        }
        
        body_text = driver.find_element(By.TAG_NAME, "body").text.lower()
        for name, keyword in modules.items():
            if keyword in body_text:
                print(f"  [PASS] Module '{name}' found on dashboard.")
            else:
                print(f"  [WARN] Module '{name}' keyword '{keyword}' not found.")
                
    except Exception as e:
        print(f"  [ERROR] {e}")
    finally:
        driver.quit()

# ── TEST 4: Profile Access ───────────────────────────────────────────────────
def test_profile_access():
    print("\n--- TEST 4: Profile Page Access ---")
    driver = get_driver()
    try:
        login_and_goto_dashboard(driver)
        
        # Navigate to profile
        driver.get(f"{URL}/profile.php")
        WebDriverWait(driver, 10).until(EC.url_contains("profile.php"))
        
        if "profile.php" in driver.current_url:
            print("  [PASS] Successfully accessed profile page.")
        else:
            print("  [FAIL] Failed to reach profile page.")
            
    except Exception as e:
        print(f"  [ERROR] {e}")
    finally:
        driver.quit()

if __name__ == "__main__":
    print("=" * 60)
    print("  PetCloud User Dashboard - Selenium Automation Tests")
    print("=" * 60)
    
    test_hero_and_greeting()
    # Wait between browser launches to avoid strain
    test_sidebar_navigation()
    test_dashboard_modules()
    test_profile_access()
    
    print("\n" + "=" * 60)
    print("  Dashboard tests completed.")
    print("=" * 60)
