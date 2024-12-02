import logging
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
from datetime import datetime, timezone
from decimal import Decimal
from concurrent.futures import ThreadPoolExecutor
import time

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Updated to comply with SQLAlchemy 2.0
Base = declarative_base()

# Database Model
class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))
    price_change = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))
    change_percent = Column(DECIMAL(10, 2), nullable=False, default=Decimal('0.00'))
    previous_close = Column(String(50), nullable=True, default="N/A")
    open = Column(String(50), nullable=True, default="N/A")
    price_low = Column(DECIMAL(20, 10), nullable=True, default=Decimal('0.0000000000'))
    price_high = Column(DECIMAL(20, 10), nullable=True, default=Decimal('0.0000000000'))
    market_cap = Column(String(50), nullable=False, default="N/A")
    circulating_supply = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    volume = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    volume_24hr = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    algorithm = Column(String(50), nullable=True, default="N/A")
    max_supply = Column(String(50), nullable=True, default="N/A")
    volume_24hr_all_currencies = Column(String(50), nullable=True, default="N/A")
    trade_time = Column(TIMESTAMP)

# Function to connect to the database
def connect_to_database(db_url):
    try:
        engine = create_engine(db_url, pool_size=5, echo=False)
        Base.metadata.create_all(engine)
        return engine
    except Exception as e:
        logging.error(f"Error connecting to database: {e}")
        return None

# Selenium function to scrape Yahoo Finance
def scrape_crypto_data(symbol):
    options = Options()
    options.add_argument('--headless')  # Run headless for performance
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    driver = None
    crypto_data = None

    try:
        driver = webdriver.Chrome(service=Service('/usr/bin/chromedriver'), options=options)
        url = f'https://finance.yahoo.com/quote/{symbol}'
        logging.info(f"Fetching data for {symbol} using Selenium")
        driver.get(url)

        WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]'))
        )

        # Initialize data dictionary
        crypto_data = {
            'name': symbol,
            'name_abreviation': symbol.split('-')[0],
            'price': Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]').text.replace(',', '')),
            'price_change': Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketChange"]').text.replace(',', '')),
            'change_percent': Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketChangePercent"]').text.replace('%', '')),
            'previous_close': "N/A",
            'open': "N/A",
            'price_low': Decimal('0.00'),
            'price_high': Decimal('0.00'),
            'market_cap': "N/A",
            'circulating_supply': Decimal('0.00'),
            'volume': Decimal('0.00'),
            'volume_24hr': Decimal('0.00'),
            'algorithm': "N/A",
            'max_supply': "N/A",
            'volume_24hr_all_currencies': "N/A",
            'trade_time': datetime.now(timezone.utc)
        }

        # Scrape summary table
        rows = driver.find_elements(By.CSS_SELECTOR, 'div#quote-summary table tbody tr')
        for row in rows:
            header = row.find_element(By.CSS_SELECTOR, 'td:first-child').text.strip()
            value = row.find_element(By.CSS_SELECTOR, 'td:last-child').text.strip()

            if 'Previous Close' in header:
                crypto_data['previous_close'] = value.replace(',', '')
            elif 'Open' in header:
                crypto_data['open'] = value.replace(',', '')
            elif "Day's Range" in header:
                low, high = value.split(' - ')
                crypto_data['price_low'] = Decimal(low.replace(',', '').strip())
                crypto_data['price_high'] = Decimal(high.replace(',', '').strip())
            elif 'Market Cap' in header:
                crypto_data['market_cap'] = value
            elif 'Circulating Supply' in header:
                crypto_data['circulating_supply'] = convert_to_decimal(value)
            elif 'Volume' in header and '24 Hr' not in header:
                crypto_data['volume'] = convert_to_decimal(value)
            elif 'Volume (24hr)' in header and 'All Currencies' not in header:
                crypto_data['volume_24hr'] = convert_to_decimal(value)

    except (TimeoutException, WebDriverException) as e:
        logging.warning(f"Error fetching data for {symbol}: {e}")
    finally:
        if driver:
            driver.quit()

    return crypto_data

# Helper function to convert values with suffixes (T, B, M) into decimals
def convert_to_decimal(value_text):
    value_text = value_text.replace(',', '').strip()
    if 'T' in value_text:
        return Decimal(value_text.replace('T', '')) * Decimal(1e12)
    elif 'B' in value_text:
        return Decimal(value_text.replace('B', '')) * Decimal(1e9)
    elif 'M' in value_text:
        return Decimal(value_text.replace('M', '')) * Decimal(1e6)
    try:
        return Decimal(value_text)
    except:
        return Decimal('0.00')

# Insert data into the database
def insert_data_into_database(engine, data):
    if engine is None:
        return

    Session = sessionmaker(bind=engine)
    session = Session()

    try:
        for item in data:
            existing = session.query(CryptoInformation).filter_by(name=item['name']).first()
            if existing:
                for key, value in item.items():
                    setattr(existing, key, value)
            else:
                session.add(CryptoInformation(**item))
        session.commit()
    except Exception as e:
        logging.error(f"Error inserting data: {e}")
        session.rollback()
    finally:
        session.close()

# Main script
if __name__ == '__main__':
    db_url = 'mysql+pymysql://user:password@localhost/crypto_express'
    engine = connect_to_database(db_url)

    if engine is None:
        exit("Failed to connect to database. Exiting.")

    # List of cryptocurrencies to scrape
    mycrypto = ['BTC-USD', 'ETH-USD', 'SOL-USD', 'XRP-USD', 'ADA-USD', 'DOGE-USD', 'BNB-USD',
                'SHIB-USD', 'DOT-USD', 'LTC-USD', 'AVAX-USD', 'MATIC-USD', 'ATOM-USD', 'TRX-USD',
                'UNI-USD', 'BCH-USD', 'XLM-USD', 'ICP-USD', 'HBAR-USD', 'VET-USD']

    # Multi-threading to speed up scraping
    with ThreadPoolExecutor(max_workers=3) as executor:  # Adjust max_workers as per your system resources
        results = list(executor.map(scrape_crypto_data, mycrypto))

    # Filter out any failed attempts
    valid_results = [res for res in results if res is not None]

    # Insert valid data into the database
    insert_data_into_database(engine, valid_results)

    logging.info("Data scraping and insertion completed successfully.")







