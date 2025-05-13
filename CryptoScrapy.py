import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, Integer, String, Float
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker

Base = declarative_base()

class CryptoData(Base):
    __tablename__ = 'crypto_data'
    id = Column(Integer, primary_key=True)
    symbol = Column(String(10), nullable=False)
    price = Column(Float, nullable=False)

def connect_to_database(db_url):
    try:
        engine = create_engine(db_url, pool_size=5, echo=True)
        return engine
    except Exception as e:
        print(f"Error connecting to database: {e}")
        return None 

def get_crypto_data(symbol):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    try:
        r = requests.get(url)
        if r.status_code == 200:
            soup = BeautifulSoup(r.text, 'html.parser')
            price_element = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketPrice'})
            if price_element:
                price = price_element.span.text
                return {'symbol': symbol, 'price': price}
            else:
                print(f"Price element not found for {symbol}")
                return None
        else:
            print(f"Failed to fetch data for {symbol}: {r.status_code}")
            return None
    except Exception as e:
        print(f"Error getting data for {symbol}: {e}")
        return None

def insert_data_into_database(engine, data):
    if engine is None:
        return

    from sqlalchemy.orm import sessionmaker 

    Session = sessionmaker(bind=engine)
    session = Session()

    try:
        session.bulk_insert_mappings(CryptoData, data)
        session.commit()
    except Exception as e:
        print(f"Error inserting data: {e}")
        session.rollback()
    finally:
        session.close()

if __name__ == '__main__':
    db_url = '' #insert your database link

    engine = connect_to_database(db_url)
    if engine is None:
        exit("Failed to connect to database. Exiting.")

    mycrypto = ['BTC-USD', 'ETH-USD', 'BNB-USD', 'SOL-USD', 'DOGE-USD']
    stockdata = []

    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)

    insert_data_into_database(engine, stockdata)

    print("Data extraction and insertion completed.")
