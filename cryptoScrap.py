import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
from datetime import datetime, timezone
import time
import logging
import re
from decimal import Decimal

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Updated to comply with SQLAlchemy 2.0
Base = declarative_base()

# Database Model
class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))  # High precision for price
    price_change = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))  # High precision
    change_percent = Column(DECIMAL(10, 2), nullable=False, default=Decimal('0.00'))
    previous_close = Column(String(50), nullable=True, default="N/A")  # Handles blanks like '--'
    open = Column(String(50), nullable=True, default="N/A")
    price_low = Column(DECIMAL(20, 10), nullable=True, default=Decimal('0.0000000000'))
    price_high = Column(DECIMAL(20, 10), nullable=True, default=Decimal('0.0000000000'))
    market_cap = Column(String(50), nullable=False, default="N/A")
    circulating_supply = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    volume = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    volume_24hr = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    algorithm = Column(String(50), nullable=True, default="N/A")  # Placeholder for blank values
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

# Function to scrape Yahoo Finance for given cryptocurrency symbols
def get_crypto_data(symbol, retries=3):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36"
    }
    attempt = 0
    while attempt < retries:
        try:
            logging.info(f"Fetching data for {symbol}, attempt {attempt + 1}")
            r = requests.get(url, headers=headers, timeout=5)
            if r.status_code == 200:
                soup = BeautifulSoup(r.text, 'html.parser')

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

                # Parse the mid-page columns
                summary_table = soup.find('div', {'data-test': 'summary-table'})  # Locate summary table
                if summary_table:
                    rows = summary_table.find_all('tr')
                    for row in rows:
                        header = row.find('td', {'class': 'C($primaryColor)'})
                        value = row.find('td', {'class': 'Ta(end)'})
                        if header and value:
                            header_text = header.text.strip()
                            value_text = value.text.strip()

                            # Column 1
                            if 'Previous Close' in header_text:
                                previous_close = value_text if value_text != '--' else "N/A"
                            elif 'Open' in header_text:
                                open_price = value_text if value_text != '--' else "N/A"
                            elif "Day's Range" in header_text:
                                if '-' in value_text:
                                    low, high = value_text.split('-')
                                    price_low = Decimal(low.replace(',', '').strip()) if low.strip() != '--' else Decimal('0.00')
                                    price_high = Decimal(high.replace(',', '').strip()) if high.strip() != '--' else Decimal('0.00')
                            
                            # Column 2
                            elif 'Algorithm' in header_text:
                                algorithm = value_text if value_text != '--' else "N/A"
                            elif 'Max Supply' in header_text:
                                max_supply = value_text if value_text != '--' else "N/A"

                            # Column 3
                            elif 'Market Cap' in header_text:
                                market_cap = value_text if value_text != '--' else "N/A"
                            elif 'Circulating Supply' in header_text:
                                circulating_supply = convert_to_decimal(value_text) if value_text != '--' else Decimal('0.00')
                            
                            # Column 4
                            elif 'Volume' in header_text and '24 Hr' not in header_text:
                                volume = convert_to_decimal(value_text) if value_text != '--' else Decimal('0.00')
                            elif 'Volume 24 Hr' in header_text and 'All Currencies' not in header_text:
                                volume_24hr = convert_to_decimal(value_text) if value_text != '--' else Decimal('0.00')
                            elif 'Volume 24 Hr (All Currencies)' in header_text:
                                volume_24hr_all_currencies = value_text if value_text != '--' else "N/A"

                # Log extracted data
                logging.info(f"Extracted data for {symbol}: "
                             f"Price={price}, Previous Close={previous_close}, Open={open_price}, "
                             f"Price Low={price_low}, Price High={price_high}, Market Cap={market_cap}, "
                             f"Circulating Supply={circulating_supply}, Volume={volume}, "
                             f"Volume 24 Hr={volume_24hr}, Algorithm={algorithm}, "
                             f"Max Supply={max_supply}, Volume (All Currencies)={volume_24hr_all_currencies}")

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
            else:
                logging.error(f"Failed to fetch data for {symbol}: HTTP {r.status_code}")
                attempt += 1
                time.sleep(2)
        except Exception as e:
            logging.error(f"Error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
    return None


# Function to convert text with suffixes like T/B/M into Decimal
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






