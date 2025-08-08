def test_footer_present():
    with open('telemedicine-html-app.html', encoding='utf-8') as f:
        content = f.read()
    assert 'id="footer"' in content
    assert '2025 Telemedicina Platform' in content
