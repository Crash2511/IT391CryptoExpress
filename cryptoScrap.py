import logging
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
from datetime import datetime, timezone
from decimal import Decimal
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

# Selenium function to get dynamic HTML
def get_dynamic_page_html(url):
    options = Options()
    options.add_argument("--headless")  # Run headless for performance
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    service = Service("path/to/chromedriver")  # Update this to the path of ChromeDriver
    driver = webdriver.Chrome(service=service, options=options)

    try:
        driver.get(url)
        time.sleep(3)  # Allow time for the page to load
        return driver.page_source
    finally:
        driver.quit()

# Function to scrape Yahoo Finance for given cryptocurrency symbols
def get_crypto_data(symbol):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    logging.info(f"Fetching data for {symbol} using Selenium")
    html = get_dynamic_page_html(url)
    
    from bs4 import BeautifulSoup
    soup = BeautifulSoup(html, 'html.parser')

    # Fetch top-level price and percent change
    price_element = soup.find('fin-streamer', {'data-field': 'regularMarketPrice'})
    price = Decimal(price_element.text.replace(',', '').strip()) if price_element else Decimal('0.0000000000')

    price_change_element = soup.find('fin-streamer', {'data-field': 'regularMarketChangePercent'})
    price_change = Decimal(re.sub(r'[^\d.-]', '', price_change_element.text)) if price_change_element else Decimal('0.00')

    # Initialize mid-page values
    previous_close = "N/A"
    open_price = "N/A"
    price_low = Decimal('0.00')
    price_high = Decimal('0.00')
    market_cap = "N/A"
    circulating_supply = Decimal('0.00')
    volume = Decimal('0.00')
    volume_24hr = Decimal('0.00')
    algorithm = "N/A"
    max_supply = "N/A"
    volume_24hr_all_currencies = "N/A"

    # Locate summary table and parse its rows
    summary_table = soup.find('div', {'data-test': 'summary-table'})
    if summary_table:
        rows = summary_table.find_all('tr')
        for row in rows:
            header = row.find('td', {'class': 'C($primaryColor)'})
            value = row.find('td', {'class': 'Ta(end)'})
            if header and value:
                header_text = header.text.strip()
                value_text = value.text.strip()
                if 'Previous Close' in header_text:
                    previous_close = value_text if value_text != '--' else "N/A"
                elif 'Open' in header_text:
                    open_price = value_text if value_text != '--' else "N/A"
                elif "Day's Range" in header_text:
                    if '-' in value_text:
                        low, high = value_text.split('-')
                        price_low = Decimal(low.replace(',', '').strip()) if low.strip() != '--' else Decimal('0.00')
                        price_high = Decimal(high.replace(',', '').strip()) if high.strip() != '--' else Decimal('0.00')
                elif 'Market Cap' in header_text:
                    market_cap = value_text if value_text != '--' else "N/A"
                elif 'Circulating Supply' in header_text:
                    circulating_supply = Decimal(value_text.replace(',', '').strip()) if value_text != '--' else Decimal('0.00')
                elif 'Volume' in header_text and '24 Hr' not in header_text:
                    volume = Decimal(value_text.replace(',', '').strip()) if value_text != '--' else Decimal('0.00')
                elif 'Volume 24 Hr' in header_text and 'All Currencies' not in header_text:
                    volume_24hr = Decimal(value_text.replace(',', '').strip()) if value_text != '--' else Decimal('0.00')
                elif 'Volume 24 Hr (All Currencies)' in header_text:
                    volume_24hr_all_currencies = value_text if value_text != '--' else "N/A"
                elif 'Algorithm' in header_text:
                    algorithm = value_text if value_text != '--' else "N/A"
                elif 'Max Supply' in header_text:
                    max_supply = value_text if value_text != '--' else "N/A"

    logging.info(f"Extracted data for {symbol}: Price={price}, Previous Close={previous_close}, "
                 f"Open={open_price}, Price Low={price_low}, Price High={price_high}, Market Cap={market_cap}, "
                 f"Circulating Supply={circulating_supply}, Volume={volume}, Volume 24 Hr={volume_24hr}, "
                 f"Algorithm={algorithm}, Max Supply={max_supply}, Volume (All Currencies)={volume_24hr_all_currencies}")

    return {
        'name': symbol,
        'name_abreviation': symbol.split('-')[0],
        'price': price,
        'price_change': price_change,
        'previous_close': previous_close,
        'open': open_price,
        'price_low': price_low,
        'price_high': price_high,
        'market_cap': market_cap,
        'circulating_supply': circulating_supply,
        'volume': volume,
        'volume_24hr': volume_24hr,
        'algorithm': algorithm,
        'max_supply': max_supply,
        'volume_24hr_all_currencies': volume_24hr_all_currencies,
        'trade_time': datetime.now(timezone.utc)
    }

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


def insert_data_into_database(engine, data):
    if engine is None:
        return

    Session = sessionmaker(bind=engine)
    session = Session()

    try:
        # Update existing records or insert new ones
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

    # Smaller list for testing
    mycrypto = ['BTC-USD', 'ETH-USD', 'SOL-USD']
    stockdata = []

    # Scrape data for each symbol
    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)

    # Insert scraped data into the database
    insert_data_into_database(engine, stockdata)

    logging.info("Data scraping and insertion completed successfully.")






