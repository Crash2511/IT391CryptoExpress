import logging
from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
from decimal import Decimal
from datetime import datetime, timezone

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Database Model
Base = declarative_base()

class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    price = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))
    price_change = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))
    change_percent = Column(DECIMAL(10, 2), nullable=False, default=Decimal('0.00'))
    previous_close = Column(String(50), nullable=True, default="N/A")
    open = Column(String(50), nullable=True, default="N/A")
    price_low = Column(DECIMAL(20, 10), nullable=True, default=Decimal('0.0000000000'))
    price_high = Column(DECIMAL(20, 10), nullable=True, default=Decimal('0.0000000000'))
    market_cap = Column(String(50), nullable=True, default="N/A")
    circulating_supply = Column(DECIMAL(20, 2), nullable=True, default=Decimal('0.00'))
    volume = Column(DECIMAL(20, 2), nullable=True, default=Decimal('0.00'))
    volume_24hr = Column(DECIMAL(20, 2), nullable=True, default=Decimal('0.00'))
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

# Function to scrape cryptocurrency data
def scrape_crypto(driver, symbol):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    driver.get(url)
    logging.info(f"Scraping data for {symbol}...")

    try:
        WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]'))
        )
    except TimeoutException:
        logging.error(f"Timeout while loading data for {symbol}. Skipping...")
        return None

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
        'volume_24hr': None,
        'trade_time': datetime.now(timezone.utc)
    }

    try:
        crypto_data['price'] = Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]').text.replace(',', ''))
        crypto_data['price_change'] = Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketChange"]').text.replace(',', ''))
        crypto_data['change_percent'] = Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketChangePercent"]').text.strip('%'))
        
        rows = driver.find_elements(By.CSS_SELECTOR, 'div#quote-summary table tbody tr')
        for row in rows:
            header = row.find_element(By.CSS_SELECTOR, 'td:first-child').text.strip()
            value = row.find_element(By.CSS_SELECTOR, 'td:last-child').text.strip()

            if 'Previous Close' in header:
                crypto_data['previous_close'] = value
            elif 'Open' in header:
                crypto_data['open'] = value
            elif "Day's Range" in header:
                low, high = value.split(' - ')
                crypto_data['price_low'] = Decimal(low.strip().replace(',', ''))
                crypto_data['price_high'] = Decimal(high.strip().replace(',', ''))
            elif 'Market Cap' in header:
                crypto_data['market_cap'] = value
            elif 'Circulating Supply' in header:
                crypto_data['circulating_supply'] = Decimal(value.replace(',', '').strip())
            elif 'Volume' in header:
                crypto_data['volume'] = Decimal(value.replace(',', '').strip())
    except NoSuchElementException as e:
        logging.warning(f"Missing element while scraping {symbol}: {e}")

    return crypto_data

# Main script
if __name__ == '__main__':
    db_url = 'mysql+pymysql://user:password@localhost/crypto_express'
    engine = connect_to_database(db_url)

    if engine is None:
        exit("Failed to connect to database. Exiting.")

    # List of cryptocurrencies to scrape
    mycrypto = ['BTC-USD', 'ETH-USD', 'SOL-USD', 'XRP-USD', 'ADA-USD']

    options = Options()
    options.add_argument('--headless')
    driver = webdriver.Chrome(service=ChromeService(ChromeDriverManager().install()), options=options)

    scraped_data = []
    for symbol in mycrypto:
        result = scrape_crypto(driver, symbol)
        if result:
            scraped_data.append(result)

    driver.quit()

    # Insert scraped data into the database
    insert_data_into_database(engine, scraped_data)
    logging.info("Data scraping and insertion completed successfully.")















