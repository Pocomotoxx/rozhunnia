def test_footer_present():
    with open('telemedicine-html-app.html', encoding='utf-8') as f:
        content = f.read()
    assert 'id="footer"' in content
    assert '2025 Telemedicina Platform' in content


def test_therapy_page_sections():
    with open('patient-therapy.html', encoding='utf-8') as f:
        content = f.read()
    assert '<h1>Terápiás lap</h1>' in content
    for token in ['id="med-list"', 'id="dis-list"', 'id="ther-list"', 'id="caregiver"']:
        assert token in content
    assert '2025 Telemedicina Platform' in content
