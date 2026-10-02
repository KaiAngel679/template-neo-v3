<div class="col-md-9">
	<div class="card">
		<div class="card-header">
			<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Transaction'); ?></div>
		</div>
		<div class="card-container">
			<div class="tables_info">
				<div class="fill_blocks">
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 68 68" xml:space="preserve">
							<g>
								<path d="M57.07 12.41H10.91C6.55 12.41 3 15.96 3 20.34v19.83h62V20.34a7.93 7.93 0 0 0-7.93-7.93zm-45.938 6.622h7.356a1 1 0 1 1 0 2h-7.356a1 1 0 1 1 0-2zm0 4.608h10.072a1 1 0 1 1 0 2H11.132a1 1 0 1 1 0-2zm13.086 6.607H11.132a1 1 0 1 1 0-2h13.086a1 1 0 1 1 0 2zm34.516-2.152c0 1.171-.95 2.121-2.122 2.121h-9.048a2.121 2.121 0 0 1-2.121-2.122V21.62c0-1.172.95-2.122 2.121-2.122h9.048c1.172 0 2.122.95 2.122 2.122zM3 47.67c0 4.37 3.55 7.92 7.91 7.92h46.16c4.38 0 7.93-3.55 7.93-7.92v-1.94H3z" fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_DonationList'); ?> (<?= number_format($lk[0]['all_cash'] ?? 0, 0, '.', ' ') . ' ' . $General->currency; ?>)
					</div>
					<div class="transaction_container">
						<?php if ($lk != true) : ?>
							<div class="empty_blocks">
								<?= $Translate->get_translate_module_phrase('module_page_profiles', '_LK_empty'); ?>
							</div>
						<?php else : ?>
							<div class="transaction_header">
								<ul>
									<li>
										<span class="hide_this">ID</span>
										<span><?= $Translate->get_translate_module_phrase('module_page_pay', '_Date') ?></span>
										<span><?= $Translate->get_translate_module_phrase('module_page_pay', '_Amount') ?></span>
										<span class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></span>
										<span class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_LK_gateway'); ?></span>
									</li>
								</ul>
							</div>
							<div class="transaction_content">
								<ul class="no-scrollbar">
									<?php for ($i = 0, $c_c = sizeof($lk); $i < $c_c; $i++) { ?>
										<li>
											<span class="hide_this"><?= $lk[$i]['pay_id'] ?></span>
											<span><?= $lk[$i]['pay_data'] ?></span>
											<span><?= $lk[$i]['pay_summ'] ?> <?= $General->currency ?></span>
											<span class="hide_this trans_svg"><?php if ($lk[$i]['pay_promo'] == ' ' || empty($lk[$i]['pay_promo'])) : ?>
													<svg viewBox="0 0 320 512">
														<path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z" />
													</svg>
												<?php else : ?>
													<?= $lk[$i]['pay_promo'] ?>
												<?php endif; ?>
											</span>
											<span class="hide_this"><img src="<?php echo $General->arr_general['site'] ?>app/modules/module_page_pay/assets/gateways/<?php echo mb_strtolower($lk[$i]['pay_system']) ?>.svg" alt="" title=""></span>
										</li>
									<?php } ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="fill_blocks">
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 32 32" xml:space="preserve" fill-rule="evenodd">
							<g>
								<path d="M18 2.003H8a3 3 0 0 0-3 3v20.341a3 3 0 0 0 4.878 2.339l.496-.398a1.002 1.002 0 0 1 1.252 0l2.498 2.006a3 3 0 0 0 3.753.003l2.5-2a.998.998 0 0 1 1.248-.001l.518.413a3 3 0 0 0 4.871-2.345V11H21a2.996 2.996 0 0 1-2.121-.879A2.996 2.996 0 0 1 18 8zM10 22h12.314a1 1 0 0 0 0-2H10a1 1 0 0 0 0 2zm0-4h12.314a1 1 0 0 0 0-2H10a1 1 0 0 0 0 2zm0-4h6a1 1 0 0 0 0-2h-6a1 1 0 0 0 0 2zM20 2.123V8a.997.997 0 0 0 1 1h5.897a2.989 2.989 0 0 0-.767-1.295l-4.854-4.829A3 3 0 0 0 20 2.123z" fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Hystory'); ?>
					</div>
					<div class="transaction_container">
						<?php if (empty($web_shop['shop'])) : ?>
							<div class="empty_blocks">
								<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Shop_empty'); ?>
							</div>
						<?php else : ?>
							<div class="transaction_header">
								<ul>
									<li>
										<?php if($web_shop['shop'] == 3) : ?>
											<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_cost') ?></span>
										<?php else : ?>
											<span>ID</span>
										<?php endif; ?>
										<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_product') ?></span>
										<?php if($web_shop['shop'] == 3) : ?>
											<span class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_quantity') ?></span>
										<?php else : ?>
											<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_status') ?></span>
										<?php endif; ?>
										<span class="hide_this"><?= $Translate->get_translate_phrase('_Date') ?></span>
									</li>
								</ul>
							</div>
							<div class="transaction_content">
								<ul class="no-scrollbar">
									<?php if($web_shop['shop'] == 3) : for ($i = 0, $c_c = sizeof($web_shop['array']); $i < $c_c; $i++) : ?>
										<?php if (($web_shop['array'][$i]['steam'] == $Player->get_steam_64())) : ?>
											<li>
												<span><?php if (isset($web_shop['array'][$i]['price'])) : ?><?= $web_shop['array'][$i]['price'] . ' ' . $General->currency; ?><?php endif; ?></span>
												<span><?= $web_shop['array'][$i]['title'] ?></span>
												<span class="hide_this"><?php if (isset($web_shop['array'][$i]['count'])) : ?><?= $web_shop['array'][$i]['count'] ?><?php endif; ?></span>
												<span class="hide_this"><?= $web_shop['array'][$i]['date'] ?></span>
											</li>
										<?php endif; ?>
									<?php endfor; else: ?>
									<?php for ($i = 0, $c_c = sizeof($web_shop['array']); $i < $c_c; $i++) : ?>
										<?php if (($web_shop['array'][$i]['steam'] == $Player->get_steam_32())) : ?>
											<li>
												<span><?php if (isset($web_shop['array'][$i]['id'])) : ?><?= $web_shop['array'][$i]['id']; ?><?php endif; ?></span>
												<span><?= $web_shop['array'][$i]['title'] ?></span>
												<span class="hide_this"><?php if (isset($web_shop['array'][$i]['status'])) : ?><?= $web_shop['array'][$i]['status'] ?><?php endif; ?></span>
												<span class="hide_this"><?= $web_shop['array'][$i]['date'] ?></span>
											</li>
										<?php endif; ?>
									<?php endfor; endif; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>