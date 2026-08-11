"""
Cifra Club API Microservice
Serviço REST em Python para extração e disponibilização de cifras do Cifra Club em formato JSON.
Padrão de rota: /artists/<artist>/songs/<song>
"""

import os
import re
from flask import Flask, jsonify, request
import requests
from bs4 import BeautifulSoup

app = Flask(__name__)

@app.route('/')
def home():
    """Rota inicial para verificação de status da API."""
    return jsonify({
        "api": "Cifra Club API",
        "status": "online",
        "version": "1.0.0"
    })

@app.route('/artists/<artist>/songs/<song>')
def get_cifra(artist, song):
    """
    Retorna a cifra, letra, tom e metadados de uma música no Cifra Club.
    """
    artist_slug = artist.lower().strip()
    song_slug   = song.lower().strip()
    url         = f"https://www.cifraclub.com.br/{artist_slug}/{song_slug}/"

    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept-Language': 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7'
    }

    try:
        res = requests.get(url, headers=headers, timeout=10, verify=False)
        if res.status_code != 200:
            return jsonify({
                "error": "Música ou artista não encontrado no Cifra Club",
                "cifraclub_url": url
            }), 404

        soup = BeautifulSoup(res.text, 'html.parser')

        # Extrai a cifra de múltiplas fontes possíveis (<pre>, div.cifra_cnt, #cifra_cnt, etc)
        cifra_text = ""
        pre_elements = soup.find_all('pre')
        if pre_elements:
            cifra_text = "\n".join([p.get_text() for p in pre_elements if p.get_text().strip()])

        if not cifra_text.strip():
            cifra_container = soup.select_one('#cifra_cnt, .cifra_cnt, .cifra_body, .cifra, .cifra-mono, article')
            if cifra_container:
                cifra_text = cifra_container.get_text()

        if not cifra_text.strip():
            return jsonify({
                "error": "Conteúdo da cifra não identificado na página",
                "cifraclub_url": url
            }), 404

        # Extrai Tom
        tom = ""
        tom_elem = soup.select_one('#cifra_tom, .cifra-tom, #cifra_tom a, span.cifra-tom')
        if tom_elem:
            tom = tom_elem.get_text().strip()

        # Extrai Nome da Música e Artista
        song_elem = soup.select_one('h1.t1, h1.title, h1')
        song_name = song_elem.get_text().strip() if song_elem else song.replace('-', ' ').title()

        artist_elem = soup.select_one('h2.t3, h2.subtitle, h2')
        artist_name = artist_elem.get_text().strip() if artist_elem else artist.replace('-', ' ').title()

        # Separa a cifra em linhas
        cifra_lines = cifra_text.splitlines()

        return jsonify({
            "artist": artist_name,
            "name": song_name,
            "tom": tom,
            "cifra": cifra_lines,
            "cifraclub_url": url
        })

    except Exception as e:
        return jsonify({
            "error": f"Falha ao processar requisição: {str(e)}",
            "cifraclub_url": url
        }), 500

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 3000))
    app.run(host='0.0.0.0', port=port)
