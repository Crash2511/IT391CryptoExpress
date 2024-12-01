import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
from datetime import datetime, timezone
import time
import logging

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Updated to comply with SQLAlchemy 2.0
Base = declarative_base()

# Database Model
class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(10, 2), nullable=False, default=0.00)
    price_change = Column(DECIMAL(10, 2), nullable=False, default=0.00)
    change_percent = Column(DECIMAL(5, 2), nullable=False, default=0.00)
    market_cap = Column(String(20), nullable=False, default="0")
    volume = Column(DECIMAL(20, 2), nullable=False, default=0.00)
    circulating_supply = Column(DECIMAL(20, 2), nullable=False, default=0.00)
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
    import requests
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
                
                # Fetch price-related fields using updated CSS selectors
                price_element = soup.find('fin-streamer', {'data-field': 'regularMarketPrice'})
                price = float(price_element.text.replace(',', '')) if price_element else 0.00

                price_change_element = soup.find('fin-streamer', {'data-field': 'regularMarketChange'})
                price_change = float(price_change_element.text.replace(',', '')) if price_change_element else 0.00

                change_percent_element = soup.find('fin-streamer', {'data-field': 'regularMarketChangePercent'})
                change_percent = float(change_percent_element.text.replace('%', '').replace(',', '')) if change_percent_element else 0.00

                # Fetch market cap, volume, and circulating supply
                market_cap = "N/A"
                volume = "0"
                circulating_supply = "0"

                # Try finding market cap, volume, and circulating supply using simplified parsing
                stats_table = soup.find_all('tr')
                for row in stats_table:
                    header = row.find('td', {'class': 'C($primaryColor) W(51%)'}).text if row.find('td', {'class': 'C($primaryColor) W(51%)'}) else None
                    value = row.find('td', {'class': 'Ta(end) Fw(600) Lh(14px)'}).text if row.find('td', {'class': 'Ta(end) Fw(600) Lh(14px)'}) else None
                    
                    if header and value:
                        if 'Market Cap' in header:
                            market_cap = value
                        elif 'Volume' in header:
                            volume = value
                        elif 'Circulating Supply' in header:
                            circulating_supply = value

                # Validate and parse extracted data
                return {
                    'name': symbol,
                    'name_abreviation': symbol.split('-')[0],
                    'price': price,
                    'price_change': price_change,
                    'change_percent': change_percent,
                    'market_cap': market_cap,
                    'volume': float(volume.replace(',', '').replace('B', '')) if volume else 0.00,
                    'circulating_supply': float(circulating_supply.replace(',', '').replace('M', '')) if circulating_supply else 0.00,
                    'trade_time': datetime.now(timezone.utc)
                }
            else:
                logging.error(f"Failed to fetch data for {symbol}: HTTP {r.status_code}")
                attempt += 1
                time.sleep(2)
        except requests.exceptions.RequestException as e:
            logging.error(f"Network error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
        except Exception as e:
            logging.error(f"Error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
    return None

# Function to insert data into the database
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
    db_url = 'mysql+pymysql://user:IT391!@localhost/crypto_express'
    engine = connect_to_database(db_url)

    if engine is None:
        exit("Failed to connect to database. Exiting.")

    # List of cryptocurrency symbols to scrape
    mycrypto = [
        'BTC-USD', 'ETH-USD', 'USDT-USD', 'SOL-USD', 'XRP-USD', 'BNB-USD', 'DOGE-USD', 'USDC-USD',
        'ADA-USD', 'SHIB-USD', 'AVAX-USD', 'TRX-USD', 'TON-USD', 'WBTC-USD', 'XLM-USD', 'DOT-USD',
        'LINK-USD', 'BCH-USD', 'SUI-USD', 'PEPE-USD', 'NEAR-USD', 'LTC-USD', 'LEO-USD', 'UNI-USD',
        'HBAR-USD', 'APT-USD', 'ICP-USD', 'DAI-USD', 'CRO-USD', 'ETC-USD', 'POL-USD', 'TAO-USD',
        'RENDER-USD', 'FET-USD', 'KAS-USD', 'FIL-USD', 'ALGO-USD', 'ARB-USD', 'VET-USD', 'STX-USD',
        'TIA-USD', 'BONK-USD', 'IMX-USD', 'ATOM-USD', 'WBT-USD', 'WIF-USD', 'OKB-USD', 'OM-USD',
        'MNT-USD', 'OP-USD'
    ]
    stockdata = []

    # Scrape data for each symbol with delay to avoid rate limiting
    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)
        time.sleep(2)  # Add delay to avoid being blocked

    # Insert scraped data into the database
    insert_data_into_database(engine, stockdata)
    logging.info("Data extraction and insertion completed.")




