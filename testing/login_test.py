from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
import time

# Credentials provided by user
EMAIL = "joshuajoseph10310@gmail.com"
PASSWORD = "admin"
URL = "http://localhost/PetCloud/index.php"

def test_login():
    """
    Automates the login process for PetCloud on localhost using Selenium.
    """
    # Setup Chrome options
    options = webdriver.ChromeOptions()
    # options.add_argument("--headless") # Uncomment to run without a browser window

    # Initialize WebDriver (automatically manages ChromeDriver)
    print("Initializing Chrome WebDriver...")
    try:
        driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    except Exception as e:
        print(f"Failed to initialize WebDriver: {e}")
        print("Make sure Google Chrome is installed and you have internet access for the first-run driver download.")
        return

    try:
        # Open the website
        print(f"Navigating to {URL}...")
        driver.get(URL)

        # Wait for page elements to be interactable
        time.sleep(2)

        # Find email and password fields based on index.php structure
        print(f"Entering email: {EMAIL}")
        email_field = driver.find_element(By.NAME, "email")
        
        print("Entering password...")
        password_field = driver.find_element(By.NAME, "password")

        # Input credentials
        email_field.send_keys(EMAIL)
        password_field.send_keys(PASSWORD)

        # Submit the form (using submit() on password field as it's part of the form)
        print("Attempting to Sign In...")
        password_field.submit()

        # Wait for redirection to complete
        time.sleep(5)

        # Validate the result by checking the current URL or page title
        current_url = driver.current_url
        print(f"Current URL after login attempt: {current_url}")

        if "dashboard" in current_url:
            print("✅ SUCCESS: Login was successful! Redirected to dashboard.")
        else:
            print("❌ FAILURE: Login failed or redirected elsewhere. Check if credentials are correct in the database.")

    except Exception as e:
        print(f"❌ An error occurred during the test: {e}")
    
    finally:
        # Keep window open for a few seconds to see the result, then close
        print("Closing browser in 5 seconds...")
        time.sleep(5)
        driver.quit()
        print("Test complete.")

if __name__ == "__main__":
    test_login()
