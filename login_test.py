from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import time

# ── Credentials ───────────────────────────────────────────────────────────────
EMAIL    = "joshuajoseph10310@gmail.com"
PASSWORD = "admin"
URL      = "http://localhost/PetCloud/index.php"

def test_login():
    print("Initializing Chrome WebDriver...")
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    try:
        driver = webdriver.Chrome(
            service=Service(ChromeDriverManager().install()), options=options
        )
    except Exception as e:
        print(f"Failed to initialize WebDriver: {e}")
        return

    try:
        print(f"Navigating to {URL}...")
        driver.get(URL)

        # Wait for email field to appear
        WebDriverWait(driver, 15).until(
            EC.presence_of_element_located((By.NAME, "email"))
        )

        print(f"Entering email: {EMAIL}")
        email_field = driver.find_element(By.NAME, "email")
        email_field.clear()
        email_field.send_keys(EMAIL)

        print("Entering password...")
        password_field = driver.find_element(By.NAME, "password")
        password_field.clear()
        password_field.send_keys(PASSWORD)

        print("Attempting to Sign In...")

        # Try clicking the submit button directly (handles JS-based logins)
        try:
            btn = driver.find_element(By.CSS_SELECTOR,
                "button[type='submit'], input[type='submit'], #login-btn, .login-btn")
            btn.click()
        except Exception:
            # Fallback: submit the form via JS
            driver.execute_script(
                "document.querySelector('form').submit();"
            )

        # Poll URL for up to 15 seconds — works for both PHP redirect & JS redirect
        print("Waiting for redirect...")
        for _ in range(30):
            time.sleep(0.5)
            cur = driver.current_url
            if "index.php" not in cur and "PetCloud" in cur:
                break
            if "dashboard" in cur or "admin" in cur:
                break

        current_url = driver.current_url
        print(f"Current URL after login attempt: {current_url}")

        if "dashboard" in current_url or "admin" in current_url:
            print("✅ SUCCESS: Login was successful! Redirected to dashboard.")
        else:
            # Maybe session set but JS redirect didn't fire — try direct nav
            print("  Trying direct navigation to admin-dashboard...")
            driver.get("http://localhost/PetCloud/admin-dashboard.php")
            time.sleep(2)
            final = driver.current_url
            print(f"  After direct nav: {final}")
            if "admin-dashboard" in final:
                print("✅ SUCCESS: Admin session confirmed via direct navigation.")
            else:
                print("❌ FAILURE: Login did not succeed. Check XAMPP is running and credentials are correct.")

    except Exception as e:
        print(f"❌ An error occurred: {e}")

    finally:
        print("Closing browser in 5 seconds...")
        time.sleep(5)
        driver.quit()
        print("Test complete.")

if __name__ == "__main__":
    test_login()
