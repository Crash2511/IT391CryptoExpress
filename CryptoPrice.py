import requests
import pandas
import sqlalchemy
apikey = '0abd643d-332d-4d9d-8ee0-87cd0c97289d'


headers = {
  'X-CMC_PRO_API_KEY' = apikey.key,
  'Accepts' = 'application/json',
}

params = {
  'start' : 1,
  'limit' : '5000',
  'convert' : 'USD;
}

url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest'

json = requests.get(url, params=params, headers=headers).json

crypto = json['data']

for x in crypto:
  print(x['symbol'],x['quote']['USD']['price'])
