from flask import Flask, render_template, request, jsonify
import requests
import os

app = Flask(__name__)

# 👉 Homepage for recommendation
@app.route('/')
def index():
    return render_template('recommend.html')


# 🧠 Handle Form-Based Recommendation
@app.route('/recommend_form', methods=['POST'])
def recommend_form():
    genre        = request.form.get('genre')
    release_type = request.form.get('release_type')
    mood         = request.form.get('mood')
    industry     = request.form.get('industry')
    year_range   = request.form.get('year_range')
    language     = request.form.get('language')

    # 🧠 Build the prompt dynamically
    prompt = f"""
    Recommend 5 {release_type.lower()} {genre} movies from the {industry} film industry.
    The movies should have a {mood.lower()} tone, preferably from the {year_range} era.
    {'Ensure the recommendations are in ' + language + '.' if language else ''}
    Respond with: Title – Short Description.
    """

    reply = call_deepseek(prompt)
    return render_template('recommend.html', form_reply=reply)



# 🤖 Handle Chatbot-Based Recommendation
@app.route('/chat_recommend', methods=['POST'])
def chat_recommend():
    data = request.get_json()
    user_msg = data.get('message')

    if not user_msg:
        return jsonify({'reply': "No message received."})

    reply = call_deepseek(user_msg)
    return jsonify({'reply': reply})


# 🔁 Call DeepSeek API via Together.ai
def call_deepseek(prompt):
    url = "https://api.together.xyz/v1/chat/completions"
    api_key = "tgp_v1_aZjgOXVlqne88LNxL9Ldcdb2wZvtiRNf0t0oSGmfVTM"  # Replace with your actual key

    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json"
    }

    payload = {
        "model": "mistralai/Mixtral-8x7B-Instruct-v0.1",  # ✅ Model
        "messages": [
            {"role": "system", "content": "You are a movie expert. Recommend movies based on user requests."},
            {"role": "user", "content": prompt}
        ],
        "temperature": 0.7
    }

    try:
        res = requests.post(url, headers=headers, json=payload)
        res.raise_for_status()
        data = res.json()
        return data['choices'][0]['message']['content']
    except Exception as e:
        return f"Error fetching recommendations: {str(e)}"



if __name__ == '__main__':
    app.run(debug=True)
