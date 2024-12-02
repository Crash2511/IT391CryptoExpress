import logging
from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from decimal import Decimal
from datetime import datetime, timezone
from sqlalchemy import create_engine, Table, Column, String, MetaData, Float, DateTime
from sqlalchemy.orm import sessionmaker
import pymysql

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Database connection URL
db_url = 'mysql+pymysql://user:password@localhost/crypto_express'
engine = create_engine(db_url)
metadata = MetaData()
Session = sessionmaker(bind=engine)

# Define a table to store cryptocurrency data
crypto_table = Table('crypto_data', metadata,
    Column('name', String(50), primary_key=True),
    Column('price', Float),
    Column('price_change', Float),
    Column('change_percent', Float),
    Column('previous_close', String(50)),
    Column('open', String(50)),
    Column('price_low', Float),
    Column('price_high', Float),
    Column('market_cap', String(50)),
    Column('circulating_supply', Float),
    Column('volume', Float),
    Column('trade_time', DateTime)
)
metadata.create_all(engine)

# Setup Selenium WebDriver
def setup_driver():
    """Setup and configure the WebDriver for scraping."""
    options = Options()
    options.headless = True  # Run in headless mode for better performance
    driver = webdriver.Chrome(service=ChromeService(executable_path='/usr/lib/chromium-browser/chromedriver'), options=options)
    return driver

# Scrape cryptocurrency data from Yahoo Finance
def scrape_crypto_data(driver, symbol):
    """Scrape cryptocurrency data for a given symbol from Yahoo Finance."""
    url = f'https://finance.yahoo.com/quote/{symbol}'
    logging.info(f'Scraping data for {symbol}...')
    
    # Initializing dictionary to store scraped data
    crypto_data = {
        'name': symbol,
        'price': None,
        'price_change': None,
        'change_percent': None,
        'previous_close': None,
        'open': None,
        'price_low': None,
        'price_high': None,
        'market_cap': None,
        'circulating_supply': None,
        'volume': None,
        'trade_time': datetime.now(timezone.utc)
    }

    try:
        driver.get(url)
        # Wait for key elements to be available
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]'))
        )

        # Scrape price-related data
        crypto_data['price'] = extract_decimal(driver, 'fin-streamer[data-field="regularMarketPrice"]')
        crypto_data['price_change'] = extract_decimal(driver, 'fin-streamer[data-field="regularMarketChange"]')
        crypto_data['change_percent'] = extract_decimal(driver, 'fin-streamer[data-field="regularMarketChangePercent"]', percentage=True)

        # Scrape summary table data
        scrape_summary_table(driver, crypto_data)
        
        # Scrape additional details
        scrape_additional_details(driver, crypto_data)

    except Exception as e:
        logging.error(f"Error scraping data for {symbol}: {e}")

    return crypto_data

# Helper function to extract and convert text to Decimal
def extract_decimal(driver, selector, percentage=False):
    """Extract numerical data from the webpage and convert to Decimal."""
    try:
        text = driver.find_element(By.CSS_SELECTOR, selector).text.replace(',', '')
        value = Decimal(text.strip('%')) if percentage else Decimal(text)
        return value
    except Exception as e:
        logging.error(f"Error extracting {selector}: {e}")
        return None

# Scrape data from the summary table
def scrape_summary_table(driver, crypto_data):
    """Scrape detailed financial data from the summary table."""
    rows = driver.find_elements(By.CSS_SELECTOR, 'div#quote-summary table tbody tr')
    for row in rows:
        columns = row.find_elements(By.TAG_NAME, 'td')
        if len(columns) >= 2:
            label = columns[0].text.strip().lower()
            value = columns[1].text.strip()

            if 'previous close' in label:
                crypto_data['previous_close'] = value
            elif 'open' in label:
                crypto_data['open'] = value
            elif 'low' in label:
                crypto_data['price_low'] = extract_decimal_from_text(value)
            elif 'high' in label:
                crypto_data['price_high'] = extract_decimal_from_text(value)
            elif 'market cap' in label:
                crypto_data['market_cap'] = value
            elif 'circulating supply' in label:
                crypto_data['circulating_supply'] = extract_decimal_from_text(value)
            elif 'volume' in label:
                crypto_data['volume'] = extract_decimal_from_text(value)

# Helper function to extract decimal values
def extract_decimal_from_text(text):
    """Extract numeric value from a string."""
    try:
        return Decimal(text.replace(',', '').replace('$', ''))
    except Exception as e:
        logging.error(f"Error converting text to Decimal: {e}")
        return None

# Scrape additional details like market cap, supply, etc.
def scrape_additional_details(driver, crypto_data):
    """Scrape additional details such as market cap and volume."""
    try:
        # Placeholder: You can add specific logic to scrape other details
        pass
    except Exception as e:
        logging.error(f"Error scraping additional details: {e}")

# Save scraped data into the database
def save_crypto_data_to_db(crypto_data):
    """Insert or update cryptocurrency data in the database."""
    session = Session()
    try:
        # Check if the cryptocurrency data already exists
        existing_data = session.query(crypto_table).filter_by(name=crypto_data['name']).first()
        if existing_data:
            # Update existing record
            for key, value in crypto_data.items():
                setattr(existing_data, key, value)
            session.commit()
            logging.info(f"Updated data for {crypto_data['name']}")
        else:
            # Insert new record
            new_data = crypto_table(**crypto_data)
            session.add(new_data)
            session.commit()
            logging.info(f"Inserted new data for {crypto_data['name']}")
    except Exception as e:
        session.rollback()
        logging.error(f"Error saving data to database: {e}")
    finally:
        session.close()

# Main execution
def main():
    driver = setup_driver()
    symbols = ['BTC-USD', 'ETH-USD', 'LTC-USD']  # Example cryptocurrency symbols
    for symbol in symbols:
        crypto_data = scrape_crypto_data(driver, symbol)
        save_crypto_data_to_db(crypto_data)
    driver.quit()

if __name__ == "__main__":
    main()









