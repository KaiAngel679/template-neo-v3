function get_translate_phrase(phrase) {
    if (!translate || !translate[phrase]) return 'No Translation';
    return translate[phrase][lang] || translate[phrase]['EN'] || 'No Translation';
}

function get_translate_module_phrase(module_id, phrase) {
    if (!translate || !translate[module_id] || !translate[module_id][phrase]) return 'No Translation';
    return translate[module_id][phrase][lang] || translate[module_id][phrase]['EN'] || 'No Translation';
}